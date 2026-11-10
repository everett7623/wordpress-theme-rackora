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

$rackora_item_ids = array_map(
	'intval',
	get_posts(
		array(
			'post_type'              => 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => 100,
			'orderby'                => 'date',
			'order'                  => 'ASC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'suppress_filters'       => false,
			'meta_key'               => '_rackora_series', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'             => $rackora_series, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		)
	)
);

if ( count( $rackora_item_ids ) < 2 ) {
	return;
}

update_meta_cache( 'post', $rackora_item_ids );
$rackora_positions = array_flip( $rackora_item_ids );

// 未填写序号的文章排在最后，同序号保持查询返回的发布时间顺序。
usort(
	$rackora_item_ids,
	static function ( $left_id, $right_id ) use ( $rackora_positions ) {
		$left_order  = get_post_meta( $left_id, '_rackora_series_order', true );
		$right_order = get_post_meta( $right_id, '_rackora_series_order', true );
		$left_order  = '' === $left_order ? PHP_INT_MAX : (int) $left_order;
		$right_order = '' === $right_order ? PHP_INT_MAX : (int) $right_order;

		if ( $left_order !== $right_order ) {
			return $left_order < $right_order ? -1 : 1;
		}
		return $rackora_positions[ $left_id ] <=> $rackora_positions[ $right_id ];
	}
);

$rackora_item_ids = array_slice( $rackora_item_ids, 0, max( 1, (int) $attributes['count'] ) );
_prime_post_caches( $rackora_item_ids, false, false );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'series-posts', 'aria-label' => __( 'Series', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<h2><span><?php esc_html_e( 'Series', 'rackora' ); ?></span><span><?php echo esc_html( $rackora_series ); ?></span></h2>
	<ol>
		<?php foreach ( $rackora_item_ids as $rackora_item_id ) : ?>
			<?php $rackora_current = $rackora_item_id === $rackora_post_id; ?>
			<li<?php echo $rackora_current ? ' class="is-current"' : ''; ?>>
				<a href="<?php echo esc_url( get_permalink( $rackora_item_id ) ); ?>"<?php echo $rackora_current ? ' aria-current="page"' : ''; ?>><?php echo wp_kses_post( get_the_title( $rackora_item_id ) ); ?></a>
			</li>
		<?php endforeach; ?>
	</ol>
</aside>
