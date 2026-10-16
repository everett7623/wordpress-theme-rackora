<?php
/**
 * 分类精选入口：读取分类元数据中选定的文章。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_category() || rackora_current_page() > 1 ) {
	return;
}

$rackora_featured = get_post( (int) get_term_meta( get_queried_object_id(), 'rackora_featured_post', true ) );
if ( ! $rackora_featured || 'publish' !== $rackora_featured->post_status ) {
	return;
}
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'featured-entry' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<small><?php esc_html_e( 'Featured', 'rackora' ); ?></small>
	<a href="<?php echo esc_url( get_permalink( $rackora_featured ) ); ?>"><?php echo wp_kses_post( get_the_title( $rackora_featured ) ); ?></a>
</aside>
