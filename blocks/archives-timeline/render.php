<?php
/**
 * 归档时间线：按年份、月份分组列出文章。
 *
 * 日期归档直接使用主查询；归档页面使用自己的分页查询，每页篇数取「设置 → 阅读」中的值。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$rackora_paged = rackora_current_page();

if ( is_date() ) {
	$rackora_posts = $wp_query->posts;
	$rackora_total = (int) $wp_query->max_num_pages;
	$rackora_own   = false;
} else {
	$rackora_query = new WP_Query(
		array(
			'post_type'           => 'post',
			'post_status'         => 'publish',
			'posts_per_page'      => max( 1, (int) get_option( 'posts_per_page' ) ),
			'paged'               => $rackora_paged,
			'ignore_sticky_posts' => true,
		)
	);
	$rackora_posts = $rackora_query->posts;
	$rackora_total = (int) $rackora_query->max_num_pages;
	$rackora_own   = true;
}

if ( ! $rackora_posts ) {
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		rackora_empty_state( __( 'The archive is empty', 'rackora' ), __( 'Published posts will be organized here by year and month.', 'rackora' ) ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
	return;
}

// 一次预热本页文章的分类缓存，避免时间线每行单独 get_the_category。
$rackora_post_ids = wp_list_pluck( $rackora_posts, 'ID' );
if ( $rackora_post_ids ) {
	update_object_term_cache( $rackora_post_ids, 'post' );
}

// 按年 → 月分组，保持查询的发布时间倒序。
$rackora_groups = array();
foreach ( $rackora_posts as $rackora_item ) {
	$rackora_year  = (int) get_the_date( 'Y', $rackora_item );
	$rackora_month = (int) get_the_date( 'n', $rackora_item );

	$rackora_groups[ $rackora_year ][ $rackora_month ][] = $rackora_item;
}
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="timeline">
		<?php foreach ( $rackora_groups as $rackora_year => $rackora_months ) : ?>
			<section class="timeline-year">
				<h2><?php echo esc_html( (string) $rackora_year ); ?></h2>
				<div class="timeline-year__months">
					<?php foreach ( $rackora_months as $rackora_month => $rackora_items ) : ?>
						<div class="timeline-month">
							<h3>
								<?php
								/* translators: %s: month number. */
								echo esc_html( sprintf( __( 'Month %s', 'rackora' ), $rackora_month ) );
								?>
							</h3>
							<ol>
								<?php foreach ( $rackora_items as $rackora_item ) : ?>
									<?php $rackora_category = rackora_primary_category( $rackora_item->ID ); ?>
									<li class="timeline-entry">
										<time class="timeline-entry__date" datetime="<?php echo esc_attr( get_the_date( 'c', $rackora_item ) ); ?>"><?php echo esc_html( get_the_date( 'm-d', $rackora_item ) ); ?></time>
										<a class="timeline-entry__title" href="<?php echo esc_url( get_permalink( $rackora_item ) ); ?>"><?php echo wp_kses_post( get_the_title( $rackora_item ) ); ?></a>
										<?php if ( $rackora_category ) : ?>
											<a class="timeline-entry__category" href="<?php echo esc_url( get_category_link( $rackora_category ) ); ?>"><?php echo esc_html( $rackora_category->name ); ?></a>
										<?php else : ?>
											<span class="timeline-entry__category timeline-entry__category--empty"><?php esc_html_e( 'Uncategorized', 'rackora' ); ?></span>
										<?php endif; ?>
									</li>
								<?php endforeach; ?>
							</ol>
						</div>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endforeach; ?>
	</div>
	<?php
	if ( $rackora_own ) {
		$rackora_base = (string) get_permalink();
		echo rackora_pagination_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			$rackora_paged,
			$rackora_total,
			$rackora_paged > 1 ? rackora_paged_url( $rackora_base, $rackora_paged - 1 ) : '',
			$rackora_paged < $rackora_total ? rackora_paged_url( $rackora_base, $rackora_paged + 1 ) : '',
			__( 'Archive pagination', 'rackora' )
		);
	}
	?>
</div>
