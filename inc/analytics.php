<?php
/**
 * 第三方访问分析：GA4、Microsoft Clarity 与百度统计。默认关闭，只加载格式有效的服务。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 主题统计脚本是否应输出。
 *
 * @return bool
 */
function rackora_analytics_active() {
	$active = (bool) rackora_option( 'analytics_enabled' ) && ! is_preview();

	if ( $active && rackora_option( 'analytics_exclude_logged_in' ) && is_user_logged_in() ) {
		$active = false;
	}

	/**
	 * 过滤主题统计脚本开关。
	 *
	 * @param bool $active 是否输出第三方统计。
	 */
	return (bool) apply_filters( 'rackora_analytics_active', $active );
}

/**
 * 输出已启用且 ID 有效的统计脚本。
 */
function rackora_render_analytics() {
	if ( ! rackora_analytics_active() ) {
		return;
	}

	$ga4 = (string) rackora_option( 'ga4_id' );
	if ( preg_match( '/^G-[A-Z0-9]+$/', $ga4 ) ) {
		wp_print_script_tag(
			array(
				'async' => true,
				'src'   => 'https://www.googletagmanager.com/gtag/js?id=' . rawurlencode( $ga4 ),
			)
		);
		wp_print_inline_script_tag(
			sprintf(
				'window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());gtag("config",%s);',
				wp_json_encode( $ga4 )
			)
		);
	}

	$clarity = (string) rackora_option( 'clarity_id' );
	if ( preg_match( '/^[A-Za-z0-9]+$/', $clarity ) ) {
		wp_print_inline_script_tag(
			sprintf(
				'(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window,document,"clarity","script",%s);',
				wp_json_encode( $clarity )
			)
		);
	}

	$baidu = (string) rackora_option( 'baidu_analytics_id' );
	if ( preg_match( '/^[a-fA-F0-9]{32}$/', $baidu ) ) {
		wp_print_inline_script_tag(
			sprintf(
				'window._hmt=window._hmt||[];(function(){var s=document.createElement("script");s.async=true;s.src="https://hm.baidu.com/hm.js?"+%s;document.head.appendChild(s);})();',
				wp_json_encode( $baidu )
			)
		);
	}
}
add_action( 'wp_head', 'rackora_render_analytics', 20 );
