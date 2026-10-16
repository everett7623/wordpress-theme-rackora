<?php
/**
 * Rackora 主题入口：定义常量并加载各功能模块。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

define( 'RACKORA_VERSION', wp_get_theme( get_template() )->get( 'Version' ) );

$rackora_modules = array(
	'icons',
	'helpers',
	'setup',
	'assets',
	'settings',
	'seo',
	'analytics',
	'meta',
	'views',
	'blocks',
);

foreach ( $rackora_modules as $rackora_module ) {
	require_once get_theme_file_path( "inc/{$rackora_module}.php" );
}

unset( $rackora_modules, $rackora_module );
