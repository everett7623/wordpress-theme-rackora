<?php
/**
 * 前台资源：样式、脚本模块、配色预渲染脚本与按需加载的核心区块样式。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 当前生效的默认配色：auto、light 或 dark。
 *
 * @return string
 */
function rackora_color_scheme() {
	$scheme = rackora_option( 'color_scheme' );
	return in_array( $scheme, array( 'auto', 'light', 'dark' ), true ) ? $scheme : 'auto';
}

/**
 * 在 <html> 上声明默认配色，供 CSS 与配色切换脚本读取。
 *
 * @param string $output  语言属性。
 * @param string $doctype 文档类型。
 * @return string
 */
function rackora_language_attributes( $output, $doctype ) {
	if ( is_admin() || is_feed() || 'html' !== $doctype ) {
		return $output;
	}
	return $output . ' data-color-scheme="' . esc_attr( rackora_color_scheme() ) . '"';
}
add_filter( 'language_attributes', 'rackora_language_attributes', 10, 2 );

/**
 * 加载全局样式与脚本模块。
 *
 * 脚本按 Script Modules 注册，模块间通过 WordPress 生成的 import map 互相引用，
 * 每个文件都带版本号；代码高亮库为动态依赖，只在正文含代码块时下载。
 */
function rackora_enqueue_assets() {
	$version = RACKORA_VERSION;

	wp_enqueue_style( 'rackora-theme', get_theme_file_uri( 'assets/css/theme.css' ), array(), $version );

	wp_register_script_module( 'rackora/shared', get_theme_file_uri( 'assets/js/shared.js' ), array(), $version );
	wp_enqueue_script_module( 'rackora/theme', get_theme_file_uri( 'assets/js/theme.js' ), array( 'rackora/shared' ), $version );

	// 目录、代码高亮与阅读进度脚本仅加载于文章（含文档模板），避免关于页等静态页白付代价。
	if ( ! is_singular( 'post' ) ) {
		return;
	}

	wp_register_script_module( 'rackora/highlight', get_theme_file_uri( 'assets/vendor/highlight.min.js' ), array(), $version );
	wp_register_script_module( 'rackora/post-toc', get_theme_file_uri( 'assets/js/post-toc.js' ), array( 'rackora/shared' ), $version );
	wp_register_script_module(
		'rackora/post-code',
		get_theme_file_uri( 'assets/js/post-code.js' ),
		array(
			'rackora/shared',
			array(
				'id'     => 'rackora/highlight',
				'import' => 'dynamic',
			),
		),
		$version
	);
	wp_enqueue_script_module(
		'rackora/post',
		get_theme_file_uri( 'assets/js/post.js' ),
		array( 'rackora/shared', 'rackora/post-toc', 'rackora/post-code' ),
		$version
	);
}
// 晚于全局样式（优先级 10）加载，同等优先级的选择器以主题样式为准。
add_action( 'wp_enqueue_scripts', 'rackora_enqueue_assets', 20 );

/**
 * 按需加载核心区块的主题样式：assets/css/blocks/{name}.css 对应 core/{name}，
 * 只有页面实际渲染该区块时才输出。
 */
function rackora_enqueue_core_block_styles() {
	$files = glob( get_theme_file_path( 'assets/css/blocks/*.css' ) );
	if ( ! $files ) {
		return;
	}

	foreach ( $files as $file ) {
		$name = basename( $file, '.css' );
		wp_enqueue_block_style(
			'core/' . $name,
			array(
				'handle' => 'rackora-core-' . $name,
				'src'    => get_theme_file_uri( 'assets/css/blocks/' . $name . '.css' ),
				'path'   => $file,
				'ver'    => RACKORA_VERSION,
			)
		);
	}
}
add_action( 'init', 'rackora_enqueue_core_block_styles' );

/**
 * 在样式之前恢复访客上次选择的配色，避免首屏闪烁。
 */
function rackora_print_color_scheme_script() {
	$script = '(function(){try{var s=localStorage.getItem("rackora-color-scheme");if(s==="light"||s==="dark"){document.documentElement.setAttribute("data-color-scheme",s);}}catch(e){}})();';
	wp_print_inline_script_tag( $script, array( 'id' => 'rackora-color-scheme' ) );

	echo '<meta name="theme-color" content="#f6f8f7" media="(prefers-color-scheme: light)" />' . "\n";
	echo '<meta name="theme-color" content="#101714" media="(prefers-color-scheme: dark)" />' . "\n";
}
add_action( 'wp_head', 'rackora_print_color_scheme_script', 2 );

/**
 * 预加载正文字体，缩短首屏文字的替换时间。
 */
function rackora_preload_fonts() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin />' . "\n",
		esc_url( get_theme_file_uri( 'assets/fonts/ibm-plex-sans-latin-400-normal.woff2' ) )
	);
}
add_action( 'wp_head', 'rackora_preload_fonts', 3 );

/**
 * 输出前台脚本配置：界面文案与阅读计数接口。
 */
function rackora_print_script_config() {
	$post_id = is_singular() ? (int) get_queried_object_id() : 0;

	$config = array(
		'postId'     => $post_id,
		'trackViews' => $post_id > 0 && rackora_views_trackable( $post_id ),
		'restUrl'    => esc_url_raw( rest_url( 'rackora/v1/views/' ) ),
		'nonce'      => is_user_logged_in() ? wp_create_nonce( 'wp_rest' ) : '',
		'i18n'       => array(
			'switchToLight' => __( 'Switch to light mode', 'rackora' ),
			'switchToDark'  => __( 'Switch to dark mode', 'rackora' ),
			'section'       => __( 'Section', 'rackora' ),
			/* translators: %s: heading text. */
			'linkTo'        => __( 'Link to %s', 'rackora' ),
			'contents'      => __( 'Contents', 'rackora' ),
			'onThisPage'    => __( 'On this page', 'rackora' ),
			'close'         => __( 'Close', 'rackora' ),
			'copyCode'      => __( 'Copy code', 'rackora' ),
			'copy'          => __( 'Copy', 'rackora' ),
			'copied'        => __( 'Copied', 'rackora' ),
			'copyFailed'    => __( 'Copy failed', 'rackora' ),
			'linkCopied'    => __( 'Link copied', 'rackora' ),
			'productTable'  => __( 'Product comparison table', 'rackora' ),
			'dataTable'     => __( 'Data table', 'rackora' ),
			'sponsored'     => __( 'Sponsored', 'rackora' ),
		),
	);

	wp_print_inline_script_tag(
		wp_json_encode( $config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
		array(
			'type' => 'application/json',
			'id'   => 'rackora-config',
		)
	);
}
add_action( 'wp_head', 'rackora_print_script_config', 4 );

/**
 * 文章与页面顶部的阅读进度条。
 */
function rackora_render_reading_progress() {
	if ( is_singular( 'post' ) ) {
		echo '<div class="reading-progress" aria-hidden="true"><span data-reading-progress></span></div>';
	}
}
add_action( 'wp_body_open', 'rackora_render_reading_progress' );

/**
 * 全站返回顶部按钮。
 */
function rackora_render_back_to_top() {
	printf(
		'<button class="back-to-top" type="button" title="%1$s" aria-label="%1$s" data-back-to-top>%2$s</button>',
		esc_attr__( 'Back to top', 'rackora' ),
		rackora_icon( 'chevron-up' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 静态内联 SVG。
	);
}
add_action( 'wp_footer', 'rackora_render_back_to_top' );
