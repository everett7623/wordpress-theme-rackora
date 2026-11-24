<?php
/**
 * 阅读计数：前台脚本在会话内对每篇内容发送一次 REST 请求，开启页面缓存时同样有效。
 *
 * 计数保存在 _rackora_views 元数据中；站点总访问量为全部计数之和，缓存一小时。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 指定内容是否参与阅读计数。
 *
 * @param int $post_id 内容 ID。
 * @return bool
 */
function rackora_views_trackable( $post_id ) {
	$post = get_post( $post_id );

	return (bool) rackora_option( 'views_enabled' )
		&& $post
		&& 'publish' === $post->post_status
		&& '' === $post->post_password
		&& is_post_type_viewable( $post->post_type )
		&& ( ! rackora_option( 'views_posts_only' ) || 'post' === $post->post_type );
}

/**
 * 读取内容的阅读数。
 *
 * @param int $post_id 内容 ID。
 * @return int
 */
function rackora_get_views( $post_id ) {
	$views = (int) get_post_meta( $post_id, '_rackora_views', true );

	/**
	 * 过滤显示的阅读数，可用于对接其他计数插件。
	 *
	 * @param int $views   阅读数。
	 * @param int $post_id 内容 ID。
	 */
	return (int) apply_filters( 'rackora_post_views', $views, $post_id );
}

/**
 * 站点总阅读数。
 *
 * @return int
 */
function rackora_total_views() {
	$total = get_transient( 'rackora_total_views' );

	if ( false === $total ) {
		global $wpdb;
		if ( rackora_option( 'views_posts_only' ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- 聚合查询，结果写入 transient 缓存。
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_status = 'publish' AND p.post_type = 'post'",
					'_rackora_views'
				)
			);
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- 聚合查询，结果写入 transient 缓存。
			$total = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_status = 'publish'",
					'_rackora_views'
				)
			);
		}
		set_transient( 'rackora_total_views', $total, HOUR_IN_SECONDS );
	}

	return (int) $total;
}

/**
 * 判断这次请求是否应计入阅读数。
 *
 * 默认跳过能编辑该内容的登录用户（作者自查不计数，前台脚本会附带 REST nonce 以识别登录状态）
 * 与明显的爬虫、无头浏览器请求；同一浏览器会话的重复访问已由前台脚本去重。
 *
 * @param WP_Post         $post    被访问的内容。
 * @param WP_REST_Request $request 计数请求。
 * @return bool
 */
function rackora_should_count_view( $post, $request ) {
	if ( current_user_can( 'edit_post', $post->ID ) ) {
		return false;
	}

	$agent = (string) $request->get_header( 'user_agent' );
	if ( '' === $agent || preg_match( '/bot|crawl|spider|slurp|headless|lighthouse|facebookexternalhit|curl|wget|python-requests/i', $agent ) ) {
		return false;
	}

	/**
	 * 过滤是否计入本次阅读。
	 *
	 * @param bool            $count   是否计数。
	 * @param WP_Post         $post    被访问的内容。
	 * @param WP_REST_Request $request 计数请求。
	 */
	return (bool) apply_filters( 'rackora_should_count_view', true, $post, $request );
}

/**
 * 已发布内容删除时清理站点总阅读数缓存。
 *
 * @param int          $post_id 内容 ID。
 * @param WP_Post|null $post    内容对象。
 */
function rackora_flush_total_views_cache( $post_id = 0, $post = null ) {
	if ( $post instanceof WP_Post && 'publish' !== $post->post_status ) {
		return;
	}
	delete_transient( 'rackora_total_views' );
}
add_action( 'deleted_post', 'rackora_flush_total_views_cache', 10, 2 );

/**
 * 仅在内容状态跨越 publish 边界时清理站点总阅读数缓存，避免日常编辑与自动保存击穿缓存。
 *
 * @param string $new_status 新状态。
 * @param string $old_status 旧状态。
 */
function rackora_flush_total_views_cache_on_transition( $new_status, $old_status ) {
	if ( $new_status === $old_status ) {
		return;
	}
	if ( 'publish' === $new_status || 'publish' === $old_status ) {
		delete_transient( 'rackora_total_views' );
	}
}
add_action( 'transition_post_status', 'rackora_flush_total_views_cache_on_transition', 10, 2 );

/**
 * 识别访客客户端 IP。
 *
 * 默认优先使用 REMOTE_ADDR，防止客户端直接伪造转发头绕过限流；仅当远程地址属于内网/保留网段
 * （如反向代理或容器网关）或通过 rackora_trust_proxy_headers 显式信任时，才读取 CDN / 代理转发头。
 *
 * @param WP_REST_Request $request 请求。
 * @return string
 */
function rackora_client_ip( $request ) {
	$remote_addr = ! empty( $_SERVER['REMOTE_ADDR'] ) ? trim( (string) wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$is_trusted_proxy = '' !== $remote_addr
		&& false !== filter_var( $remote_addr, FILTER_VALIDATE_IP )
		&& false === filter_var( $remote_addr, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE );

	/**
	 * 过滤是否信任 CDN / 反向代理转发的客户端 IP 请求头。
	 *
	 * @param bool            $trust       是否信任转发头。
	 * @param string          $remote_addr 原始 REMOTE_ADDR。
	 * @param WP_REST_Request $request     REST 请求。
	 */
	$trust_headers = (bool) apply_filters( 'rackora_trust_proxy_headers', $is_trusted_proxy, $remote_addr, $request );

	$candidates = $trust_headers
		? array(
			$request->get_header( 'cf_connecting_ip' ),
			$request->get_header( 'x_real_ip' ),
			$request->get_header( 'x_forwarded_for' ),
			$remote_addr,
		)
		: array( $remote_addr );

	$ip = '';
	foreach ( $candidates as $raw ) {
		if ( ! is_string( $raw ) || '' === trim( $raw ) ) {
			continue;
		}
		$first = trim( explode( ',', $raw )[0] );
		if ( false !== filter_var( $first, FILTER_VALIDATE_IP ) ) {
			$ip = $first;
			break;
		}
	}

	if ( '' === $ip ) {
		$ip = 'unknown';
	}

	/**
	 * 过滤用于阅读防刷限流的访客 IP。
	 *
	 * @param string          $ip      客户端 IP。
	 * @param WP_REST_Request $request REST 请求。
	 */
	return (string) apply_filters( 'rackora_client_ip', $ip, $request );
}

/**
 * 同一访客对同一篇文章的服务端限流（按 IP，一小时内只计一次）。
 *
 * @param int             $post_id 文章 ID。
 * @param WP_REST_Request $request 请求。
 * @return bool 为 true 表示应跳过计数。
 */
function rackora_view_rate_limited( $post_id, $request ) {
	$ip = rackora_client_ip( $request );

	$key = 'rackora_vr_' . md5( $ip . '|' . (int) $post_id );
	if ( get_transient( $key ) ) {
		return true;
	}

	set_transient( $key, 1, HOUR_IN_SECONDS );
	return false;
}

/**
 * 注册计数接口：POST /wp-json/rackora/v1/views/{id}。
 */
function rackora_register_views_route() {
	register_rest_route(
		'rackora/v1',
		'/views/(?P<id>\d+)',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'rackora_rest_count_view',
			'permission_callback' => '__return_true',
			'args'                => array(
				'id' => array(
					'type'     => 'integer',
					'required' => true,
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'rackora_register_views_route' );

/**
 * 计数接口回调。原子递增，避免并发请求互相覆盖。
 *
 * @param WP_REST_Request $request 请求。
 * @return WP_REST_Response|WP_Error
 */
function rackora_rest_count_view( $request ) {
	$post = get_post( (int) $request['id'] );

	if ( ! $post || ! rackora_views_trackable( $post->ID ) ) {
		return new WP_Error( 'rackora_views_unavailable', __( 'Views are not counted for this content.', 'rackora' ), array( 'status' => 404 ) );
	}

	if ( ! rackora_should_count_view( $post, $request ) ) {
		return rest_ensure_response(
			array(
				'counted' => false,
				'views'   => rackora_get_views( $post->ID ),
			)
		);
	}

	if ( rackora_view_rate_limited( $post->ID, $request ) ) {
		return rest_ensure_response(
			array(
				'counted' => false,
				'views'   => rackora_get_views( $post->ID ),
			)
		);
	}

	global $wpdb;
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- 原子递增计数，随后清理元数据缓存。
	$updated = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->postmeta} SET meta_value = meta_value + 1 WHERE post_id = %d AND meta_key = %s", $post->ID, '_rackora_views' ) );
	if ( ! $updated ) {
		add_post_meta( $post->ID, '_rackora_views', 1, true );
	}
	wp_cache_delete( $post->ID, 'post_meta' );

	$total = get_transient( 'rackora_total_views' );
	if ( false !== $total ) {
		set_transient( 'rackora_total_views', (int) $total + 1, HOUR_IN_SECONDS );
	}

	return rest_ensure_response(
		array(
			'counted' => true,
			'views'   => rackora_get_views( $post->ID ),
		)
	);
}
