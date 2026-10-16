<?php
/**
 * 文章系列：列出同一系列名称的已发布文章，按系列序号、发布时间排序。
 *
 * @package Rackora
 * @var array    $attributes 区块属性。
 * @var WP_Block $block      区块实例。
 */

defined( 'ABSPATH' ) || exit;

$rackora_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$rackora_series  = trim( (string) get_post_meta( $rackora_post_id, '_rackora_series', true ) );

if ( ! $rackora_post_id || '' === $rackora_series ) {
	return;
}

$rackora_items = get_posts(
	array(
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'posts_per_page'   => 100,
		'orderby'          => 'date',
		'order'            => 'ASC',
		'no_found_rows'    => true,
		'suppress_filters' => false,
		'meta_key'         => '_rackora_series', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'       => $rackora_series, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	)
);

if ( count( $rackora_items ) < 2 ) {
	return;
}

// 未填写序号的文章排在最后，同序号保持发布时间顺序（usort 自 PHP 8 起稳定，这里显式比较时间以兼容旧版本）。
usort(
	$rackora_items,
	static function ( $left, $right ) {
		$left_order  = get_post_meta( $left->ID, '_rackora_series_order', true );
		$right_order = get_post_meta( $right->ID, '_rackora_series_order', true );
		$left_order  = '' === $left_order ? PHP_INT_MAX : (int) $left_order;
		$right_order = '' === $right_order ? PHP_INT_MAX : (int) $right_order;

		if ( $left_order !== $right_order ) {
			return $left_order < $right_order ? -1 : 1;
		}
		return strcmp( $left->post_date_gmt, $right->post_date_gmt );
	}
);

$rackora_items = array_slice( $rackora_items, 0, max( 1, (int) $attributes['count'] ) );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'series-posts', 'aria-label' => __( 'Series', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<h2><span><?php esc_html_e( 'Series', 'rackora' ); ?></span><span><?php echo esc_html( $rackora_series ); ?></span></h2>
	<ol>
		<?php foreach ( $rackora_items as $rackora_item ) : ?>
			<?php $rackora_current = (int) $rackora_item->ID === $rackora_post_id; ?>
			<li<?php echo $rackora_current ? ' class="is-current"' : ''; ?>>
				<a href="<?php echo esc_url( get_permalink( $rackora_item ) ); ?>"<?php echo $rackora_current ? ' aria-current="page"' : ''; ?>><?php echo wp_kses_post( get_the_title( $rackora_item ) ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</aside>
