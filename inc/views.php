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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery -- 聚合查询，结果写入 transient 缓存。
		$total = (int) $wpdb->get_var( $wpdb->prepare( "SELECT SUM(meta_value) FROM {$wpdb->postmeta} WHERE meta_key = %s", '_rackora_views' ) );
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
 * 同一访客对同一篇文章的服务端限流（按 IP，一小时内只计一次）。
 *
 * @param int             $post_id 文章 ID。
 * @param WP_REST_Request $request 请求。
 * @return bool 为 true 表示应跳过计数。
 */
function rackora_view_rate_limited( $post_id, $request ) {
	$ip = '';
	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = (string) wp_unslash( $_SERVER['REMOTE_ADDR'] );
	}
	if ( '' === $ip ) {
		$ip = 'unknown';
	}

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
