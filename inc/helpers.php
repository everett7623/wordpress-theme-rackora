<?php
/**
 * 通用辅助函数：设置读取、图标、链接、计数与文案。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 主题设置默认值（外观 → Rackora）。
 *
 * 只保存 WordPress 没有原生位置的选项；配色、字体、版宽在「样式」中调整，
 * 页面结构在站点编辑器中调整。
 *
 * @return array<string, mixed>
 */
function rackora_settings_defaults() {
	$social = array();
	foreach ( array_keys( rackora_social_platforms() ) as $key ) {
		$social[ $key ] = '';
	}

	return array(
		'color_scheme'        => 'auto',
		'views_enabled'       => true,
		'views_posts_only'    => false,
		'launch_date'         => '',
		'icp_number'          => '',
		'icp_url'             => 'https://beian.miit.gov.cn/',
		'psb_number'          => '',
		'psb_url'             => '',
		'license_name'        => 'CC BY-NC-SA 4.0',
		'license_url'         => 'https://creativecommons.org/licenses/by-nc-sa/4.0/',
		'share_targets'       => array( 'twitter', 'facebook', 'linkedin', 'reddit', 'telegram', 'whatsapp', 'weibo', 'email' ),
		'share_copy'          => true,
		'ad_disclosure'       => '',
		'seo_enabled'         => true,
		'seo_description'     => '',
		'noindex_tags'        => true,
		'noindex_archives'    => true,
		'noindex_authors'     => true,
		'hide_draft_links'    => true,
		'google_verification' => '',
		'bing_verification'   => '',
		'baidu_verification'  => '',
		'organization_name'   => '',
		'organization_logo'   => '',
		'about_url'           => '',
		'social_profiles'     => $social,
		'social_other'        => '',
		'analytics_enabled'          => false,
		'analytics_exclude_logged_in' => true,
		'ga4_id'                     => '',
		'clarity_id'          => '',
		'baidu_analytics_id'  => '',
	);
}

/**
 * 社交主页预设平台（用于设置页与 Organization sameAs）。
 *
 * @return array<string, string> 平台键 => 显示名。
 */
function rackora_social_platforms() {
	return array(
		'github'    => 'GitHub',
		'x'         => 'X',
		'weibo'     => __( 'Weibo', 'rackora' ),
		'bilibili'  => 'Bilibili',
		'zhihu'     => __( 'Zhihu', 'rackora' ),
		'telegram'  => 'Telegram',
		'youtube'   => 'YouTube',
		'mastodon'  => 'Mastodon',
		'linkedin'  => 'LinkedIn',
		'email'     => __( 'Email', 'rackora' ),
	);
}

/**
 * 根据 URL 猜测社交平台键，无法识别时返回空字符串。
 *
 * @param string $url 完整地址。
 * @return string
 */
function rackora_guess_social_platform( $url ) {
	$host = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
	$host = preg_replace( '/^www\./', '', $host );

	$map = array(
		'github.com'        => 'github',
		'x.com'             => 'x',
		'twitter.com'       => 'x',
		'weibo.com'         => 'weibo',
		'bilibili.com'      => 'bilibili',
		'space.bilibili.com'=> 'bilibili',
		'zhihu.com'         => 'zhihu',
		't.me'              => 'telegram',
		'telegram.me'       => 'telegram',
		'youtube.com'       => 'youtube',
		'youtu.be'          => 'youtube',
		'linkedin.com'      => 'linkedin',
	);

	if ( isset( $map[ $host ] ) ) {
		return $map[ $host ];
	}

	if ( false !== strpos( $host, 'mastodon.' ) || preg_match( '/^mastodon\./', $host ) ) {
		return 'mastodon';
	}

	return '';
}

/**
 * Organization sameAs 地址列表：预设平台 + 其他 + 旧版 same_as 兼容。
 *
 * @return string[]
 */
function rackora_same_as_urls() {
	$urls     = array();
	$profiles = rackora_option( 'social_profiles' );
	if ( ! is_array( $profiles ) ) {
		$profiles = array();
	}

	foreach ( array_keys( rackora_social_platforms() ) as $key ) {
		$url = isset( $profiles[ $key ] ) ? trim( (string) $profiles[ $key ] ) : '';
		if ( '' !== $url ) {
			$urls[] = $url;
		}
	}

	$other = (string) rackora_option( 'social_other' );
	foreach ( preg_split( '/\r\n|\r|\n/', $other ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$urls[] = $line;
		}
	}

	// 旧版「每行一个」文本框：尚未迁移到预设槽时继续输出。
	if ( ! $urls ) {
		$saved = get_option( 'rackora_settings', array() );
		if ( is_array( $saved ) && ! empty( $saved['same_as'] ) ) {
			foreach ( preg_split( '/\r\n|\r|\n/', (string) $saved['same_as'] ) as $line ) {
				$line = trim( $line );
				if ( '' !== $line ) {
					$urls[] = $line;
				}
			}
		}
	}

	$urls = array_values(
		array_unique(
			array_filter(
				array_map( 'esc_url_raw', $urls ),
				static function ( $url ) {
					return (bool) preg_match( '#^https?://#i', $url );
				}
			)
		)
	);

	return $urls;
}

/**
 * 读取单个主题设置，未保存时回退到默认值。
 *
 * @param string $key 设置键名。
 * @return mixed
 */
function rackora_option( $key ) {
	$saved = get_option( 'rackora_settings', array() );
	if ( is_array( $saved ) && array_key_exists( $key, $saved ) ) {
		return $saved[ $key ];
	}

	$defaults = rackora_settings_defaults();
	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : null;
}

/**
 * 输出内联 Lucide 图标。
 *
 * @param string $name 图标名。
 * @param int    $size 显示尺寸（像素）。
 * @return string
 */
function rackora_icon( $name, $size = 18 ) {
	$nodes = rackora_icon_nodes();
	if ( ! isset( $nodes[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="rackora-icon" xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		(int) $size,
		$nodes[ $name ]
	);
}

/**
 * 主题默认图标地址，用于 Logo、站点图标与结构化数据的最终回退。
 *
 * @return string
 */
function rackora_mark_url() {
	return get_theme_file_uri( 'assets/images/rackora-mark.svg' );
}

/**
 * 站点 Logo 图片地址：站点 Logo → 主题默认图标。
 *
 * @return string
 */
function rackora_site_logo_url() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$logo    = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	return $logo ? $logo : rackora_mark_url();
}

/**
 * 查找使用指定 Rackora 页面模板的已发布页面。
 *
 * 优先匹配手动选择了 page-{$template} 模板的页面，其次匹配别名为 $template 的页面
 * （模板层级会自动为其套用 page-{$template}）。
 *
 * @param string $template 模板名：archives、categories、tags、links、about。
 * @return int 页面 ID，未找到时为 0。
 */
function rackora_template_page_id( $template ) {
	static $cache = array();

	if ( array_key_exists( $template, $cache ) ) {
		return $cache[ $template ];
	}

	$ids = get_posts(
		array(
			'post_type'        => 'page',
			'post_status'      => 'publish',
			'posts_per_page'   => 1,
			'fields'           => 'ids',
			'no_found_rows'    => true,
			'suppress_filters' => false,
			'meta_key'         => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'       => 'page-' . $template, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	);

	$page_id = $ids ? (int) $ids[0] : 0;
	if ( ! $page_id ) {
		$page = get_page_by_path( $template );
		if ( $page && 'publish' === $page->post_status ) {
			$page_id = (int) $page->ID;
		}
	}

	$cache[ $template ] = $page_id;
	return $page_id;
}

/**
 * 指定 Rackora 页面模板对应页面的链接，未创建时返回空字符串。
 *
 * @param string $template 模板名。
 * @return string
 */
function rackora_template_page_url( $template ) {
	$page_id = rackora_template_page_id( $template );
	return $page_id ? (string) get_permalink( $page_id ) : '';
}

/**
 * 判断当前页面是否使用了指定的 Rackora 页面模板。
 *
 * @param string $template 模板名。
 * @return bool
 */
function rackora_is_template_page( $template ) {
	if ( ! is_page() ) {
		return false;
	}
	return (int) get_queried_object_id() === rackora_template_page_id( $template )
		|| 'page-' . $template === get_page_template_slug( get_queried_object_id() );
}

/**
 * 当前分页页码（从 1 开始）。
 *
 * @return int
 */
function rackora_current_page() {
	return max( 1, (int) get_query_var( 'paged' ) );
}

/**
 * 为列表页地址追加分页段，兼容朴素链接与固定链接。
 *
 * @param string $base  第 1 页地址。
 * @param int    $paged 目标页码。
 * @return string
 */
function rackora_paged_url( $base, $paged ) {
	global $wp_rewrite;

	$paged = (int) $paged;
	if ( $paged <= 1 || '' === $base ) {
		return $base;
	}

	if ( $wp_rewrite->using_permalinks() && false === strpos( $base, '?' ) ) {
		return user_trailingslashit( trailingslashit( $base ) . $wp_rewrite->pagination_base . '/' . $paged, 'paged' );
	}

	return add_query_arg( 'paged', $paged, $base );
}

/**
 * 文章的主分类（第一个分类）。
 *
 * @param int $post_id 文章 ID。
 * @return WP_Term|null
 */
function rackora_primary_category( $post_id ) {
	$categories = get_the_category( $post_id );
	return $categories ? $categories[0] : null;
}

/**
 * 去除空白后按字符数估算阅读时长（分钟）。
 *
 * 中文界面按每分钟 450 字、其他语言按每分钟 900 字符计算，与 Halo 版保持一致。
 *
 * @param int|WP_Post|null $post 文章。
 * @return int
 */
function rackora_reading_minutes( $post = null ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return 0;
	}

	$text  = wp_strip_all_tags( strip_shortcodes( $post->post_content ) );
	$text  = (string) preg_replace( '/\s+/u', '', $text );
	$speed = 0 === strpos( determine_locale(), 'zh' ) ? 450 : 900;

	return max( 1, (int) ceil( mb_strlen( $text ) / $speed ) );
}

/**
 * 文章是否在发布后被修改过。
 *
 * @param int|WP_Post|null $post 文章。
 * @return bool
 */
function rackora_is_modified( $post = null ) {
	return (int) get_post_modified_time( 'U', true, $post ) > (int) get_post_time( 'U', true, $post );
}

/**
 * 「N 阅读」文案。
 *
 * @param int $count 阅读数。
 * @return string
 */
function rackora_views_label( $count ) {
	/* translators: %s: number of views. */
	return sprintf( _n( '%s view', '%s views', $count, 'rackora' ), number_format_i18n( $count ) );
}

/**
 * 「N 评论」文案。
 *
 * @param int $count 评论数。
 * @return string
 */
function rackora_comments_label( $count ) {
	/* translators: %s: number of comments. */
	return sprintf( _n( '%s comment', '%s comments', $count, 'rackora' ), number_format_i18n( $count ) );
}

/**
 * 「N 篇相关内容」文案。
 *
 * @param int $count 文章数。
 * @return string
 */
function rackora_related_count_label( $count ) {
	/* translators: %s: number of posts. */
	return sprintf( _n( '%s related post', '%s related posts', $count, 'rackora' ), number_format_i18n( $count ) );
}

/**
 * 输出空状态提示。
 *
 * @param string $title       标题。
 * @param string $description 说明。
 * @return string
 */
function rackora_empty_state( $title, $description ) {
	return sprintf(
		'<div class="empty-state"><h2>%1$s</h2><p>%2$s</p></div>',
		esc_html( $title ),
		esc_html( $description )
	);
}

/**
 * 判断当前是否处于编辑器的服务端渲染预览请求中。
 *
 * @return bool
 */
function rackora_is_editor_preview() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * 将标题文本转为 URL 锚点片段。
 *
 * @param string $text 标题文本。
 * @return string
 */
function rackora_slugify_heading( $text ) {
	$text = strtolower( trim( wp_strip_all_tags( $text ) ) );
	$text = preg_replace( '/[^\p{L}\p{N}\s-]+/u', '', $text );
	$text = preg_replace( '/[\s_]+/u', '-', $text );
	$text = preg_replace( '/-+/', '-', $text );
	$text = trim( $text, '-' );
	return '' !== $text ? $text : 'section';
}

/**
 * 为 HTML 中的 H2 / H3 注入稳定 id，并返回目录大纲。
 *
 * @param string $html 文章 HTML。
 * @return array{0: string, 1: array<int, array{level: int, id: string, text: string}>}
 */
function rackora_inject_heading_ids( $html ) {
	$outline = array();
	$used    = array();

	$html = preg_replace_callback(
		'/<h([23])(\s[^>]*)?>(.*?)<\/h\1>/is',
		static function ( $matches ) use ( &$outline, &$used ) {
			$level = (int) $matches[1];
			$attrs = (string) $matches[2];
			$inner = $matches[3];
			$text  = trim( wp_strip_all_tags( $inner ) );
			if ( '' === $text ) {
				return $matches[0];
			}

			$id = '';
			if ( preg_match( '/\sid=(["\'])([^"\']+)\1/i', $attrs, $id_match ) ) {
				$id = $id_match[2];
			}

			$base = '' !== $id ? $id : rackora_slugify_heading( $text );
			$candidate = $base;
			$n         = 2;
			while ( isset( $used[ $candidate ] ) ) {
				$candidate = $base . '-' . $n;
				++$n;
			}
			$used[ $candidate ] = true;
			$id                 = $candidate;

			if ( preg_match( '/\sid=/i', $attrs ) ) {
				$attrs = preg_replace( '/\sid=(["\'])[^"\']*\1/i', ' id="' . esc_attr( $id ) . '"', $attrs, 1 );
			} else {
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			$outline[] = array(
				'level' => $level,
				'id'    => $id,
				'text'  => $text,
			);

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$html
	);

	return array( $html, $outline );
}

/**
 * 当前文章的目录大纲（请求内缓存，与正文标题 id 共用）。
 *
 * @param int|null $post_id 文章 ID。
 * @return array<int, array{level: int, id: string, text: string}>
 */
function rackora_post_heading_outline( $post_id = null ) {
	$post_id = $post_id ? (int) $post_id : (int) get_the_ID();
	if ( $post_id <= 0 ) {
		return array();
	}

	$global_key = 'rackora_heading_outline_' . $post_id;
	if ( ! empty( $GLOBALS[ $global_key ] ) && is_array( $GLOBALS[ $global_key ] ) ) {
		return $GLOBALS[ $global_key ];
	}

	static $cache = array();
	if ( isset( $cache[ $post_id ] ) ) {
		return $cache[ $post_id ];
	}

	$post = get_post( $post_id );
	if ( ! $post ) {
		$cache[ $post_id ] = array();
		return array();
	}

	$html = do_blocks( $post->post_content );
	list( , $outline )     = rackora_inject_heading_ids( $html );
	$cache[ $post_id ]     = $outline;
	$GLOBALS[ $global_key ] = $outline;
	return $outline;
}

/**
 * 按既有大纲顺序为正文 H2 / H3 写入相同 id。
 *
 * @param string                                                               $html    正文 HTML。
 * @param array<int, array{level: int, id: string, text: string}> $outline 大纲。
 * @return string
 */
function rackora_apply_heading_outline_ids( $html, $outline ) {
	$index = 0;
	return preg_replace_callback(
		'/<h([23])(\s[^>]*)?>(.*?)<\/h\1>/is',
		static function ( $matches ) use ( &$index, $outline ) {
			if ( ! isset( $outline[ $index ] ) ) {
				return $matches[0];
			}
			$level = (int) $matches[1];
			$attrs = (string) $matches[2];
			$inner = $matches[3];
			$id    = $outline[ $index ]['id'];
			++$index;

			if ( preg_match( '/\sid=/i', $attrs ) ) {
				$attrs = preg_replace( '/\sid=(["\'])[^"\']*\1/i', ' id="' . esc_attr( $id ) . '"', $attrs, 1 );
			} else {
				$attrs .= ' id="' . esc_attr( $id ) . '"';
			}

			return '<h' . $level . $attrs . '>' . $inner . '</h' . $level . '>';
		},
		$html
	);
}

/**
 * 在正文输出时注入与目录一致的标题 id。
 *
 * @param string $content 正文 HTML。
 * @return string
 */
function rackora_annotate_content_headings( $content ) {
	if ( ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post_id    = (int) get_the_ID();
	$global_key = 'rackora_heading_outline_' . $post_id;

	if ( ! empty( $GLOBALS[ $global_key ] ) && is_array( $GLOBALS[ $global_key ] ) ) {
		return rackora_apply_heading_outline_ids( $content, $GLOBALS[ $global_key ] );
	}

	list( $content, $outline ) = rackora_inject_heading_ids( $content );
	if ( $post_id > 0 ) {
		$GLOBALS[ $global_key ] = $outline;
	}
	return $content;
}
add_filter( 'the_content', 'rackora_annotate_content_headings', 12 );
