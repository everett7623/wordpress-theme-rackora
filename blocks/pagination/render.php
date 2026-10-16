<?php
/**
 * 主查询分页：上一页 / 当前页码 / 下一页。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$rackora_total = (int) $wp_query->max_num_pages;
$rackora_paged = rackora_current_page();

if ( $rackora_total < 2 ) {
	return;
}

$rackora_markup = rackora_pagination_markup(
	$rackora_paged,
	$rackora_total,
	$rackora_paged > 1 ? (string) get_previous_posts_page_link() : '',
	$rackora_paged < $rackora_total ? (string) get_next_posts_page_link( $rackora_total ) : ''
);

printf( '<div %1$s>%2$s</div>', get_block_wrapper_attributes(), $rackora_markup ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
