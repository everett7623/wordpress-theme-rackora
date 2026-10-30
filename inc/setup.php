<?php
/**
 * 主题初始化：语言包、编辑器样式、模式分类、Logo 回退与精简输出。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 加载翻译并声明编辑器样式。
 *
 * 区块主题已自动启用 post-thumbnails、responsive-embeds、editor-styles、html5 与
 * automatic-feed-links，这里只补充主题自身需要的部分。
 */
function rackora_setup() {
	load_theme_textdomain( 'rackora', get_template_directory() . '/languages' );

	// 页面的手动摘要用作关于页导语与归档、分类等索引页的简介。
	add_post_type_support( 'page', 'excerpt' );

	add_editor_style(
		array(
			'assets/css/theme.css',
			'assets/css/blocks/post-content.css',
		)
	);
}
add_action( 'after_setup_theme', 'rackora_setup' );

/**
 * 注册主题的区块模式分类。
 */
function rackora_register_pattern_categories() {
	register_block_pattern_category(
		'rackora',
		array(
			'label'       => __( 'Rackora', 'rackora' ),
			'description' => __( 'Layouts and components provided by the Rackora theme.', 'rackora' ),
		)
	);
}
add_action( 'init', 'rackora_register_pattern_categories' );

/**
 * 启用 WordPress 自带的链接管理器，作为友情链接页面的数据来源。
 *
 * 链接分类对应 Halo 版的友链分组。
 */
add_filter( 'pre_option_link_manager_enabled', '__return_true' );

/**
 * 未设置站点 Logo 时回退到主题默认图标，与 Halo 版的 Logo 回退顺序一致。
 *
 * 站点 Logo 区块通过 get_custom_logo() 取图，因此页头、资料卡与关于页都会生效。
 * 不需要 Logo 时，在站点编辑器中移除站点 Logo 区块即可。
 *
 * @param string $html 原始 Logo 标记。
 * @return string
 */
function rackora_custom_logo_fallback( $html ) {
	if ( '' !== $html || is_admin() ) {
		return $html;
	}

	return sprintf(
		'<a href="%1$s" class="custom-logo-link rackora-logo-fallback" rel="home"><img class="custom-logo" src="%2$s" width="64" height="64" alt="%3$s" decoding="async" /></a>',
		esc_url( home_url( '/' ) ),
		esc_url( rackora_mark_url() ),
		esc_attr( get_bloginfo( 'name', 'display' ) )
	);
}
add_filter( 'get_custom_logo', 'rackora_custom_logo_fallback' );

/**
 * 未设置站点图标时输出主题 SVG 图标作为 favicon。
 */
function rackora_favicon_fallback() {
	if ( has_site_icon() ) {
		return;
	}
	printf(
		'<link rel="icon" type="image/svg+xml" href="%s" />' . "\n",
		esc_url( rackora_mark_url() )
	);
}
add_action( 'wp_head', 'rackora_favicon_fallback', 99 );

/**
 * 摘要结尾使用省略号，与文章卡片的两行截断配合。
 *
 * @return string
 */
function rackora_excerpt_more() {
	return '…';
}
add_filter( 'excerpt_more', 'rackora_excerpt_more' );

/**
 * 移除表情检测脚本与样式：现代浏览器均原生支持 emoji，可减少每页的内联脚本。
 *
 * 需要旧版表情图片回退时，用 add_filter( 'rackora_disable_emoji', '__return_false' ) 恢复。
 */
function rackora_disable_emoji() {
	if ( ! apply_filters( 'rackora_disable_emoji', true ) ) {
		return;
	}
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	add_filter( 'emoji_svg_url', '__return_false' );
}
add_action( 'init', 'rackora_disable_emoji' );

/**
 * 正文中指向尚未发布文章的站内链接：列表项整项隐藏，行内链接退化为纯文本。
 *
 * 适合知识库分批发布；目标文章发布后自动恢复。
 *
 * @param string $content 正文 HTML。
 * @return string
 */
function rackora_hide_unpublished_internal_links( $content ) {
	if ( ! rackora_option( 'hide_draft_links' ) || is_admin() || is_preview() || false === strpos( $content, 'href' ) ) {
		return $content;
	}

	$home = preg_quote( untrailingslashit( home_url() ), '#' );
	$link = '<a\s[^>]*href="(?:' . $home . ')?/([a-z0-9-]+)/?"[^>]*>(.*?)</a>';

	$hidden = static function ( $slug ) {
		static $cache = array();
		if ( ! isset( $cache[ $slug ] ) ) {
			$post           = get_page_by_path( $slug, OBJECT, 'post' );
			$cache[ $slug ] = $post instanceof WP_Post && 'publish' !== $post->post_status;
		}
		return $cache[ $slug ];
	};

	$content = preg_replace_callback(
		'#<li>\s*' . $link . '[^<]*</li>\s*#is',
		static function ( $m ) use ( $hidden ) {
			return $hidden( $m[1] ) ? '' : $m[0];
		},
		$content
	);

	return preg_replace_callback(
		'#' . $link . '#is',
		static function ( $m ) use ( $hidden ) {
			return $hidden( $m[1] ) ? $m[2] : $m[0];
		},
		$content
	);
}
add_filter( 'the_content', 'rackora_hide_unpublished_internal_links', 20 );
