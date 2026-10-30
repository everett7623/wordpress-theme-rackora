<?php
/**
 * 文章卡片：置顶标签、主分类、日期、标题、摘要、阅读与评论数。
 *
 * @package Rackora
 * @var array    $attributes 区块属性。
 * @var WP_Block $block      区块实例。
 */

defined( 'ABSPATH' ) || exit;

$rackora_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$rackora_post    = get_post( $rackora_post_id );

if ( ! $rackora_post ) {
	return;
}

$rackora_link     = get_permalink( $rackora_post );
$rackora_title    = get_the_title( $rackora_post );
$rackora_pinned   = is_sticky( $rackora_post->ID );
$rackora_category = rackora_primary_category( $rackora_post->ID );
$rackora_cover    = $attributes['showCover'] && has_post_thumbnail( $rackora_post );
$rackora_classes  = 'post-entry' . ( $rackora_pinned ? ' is-pinned' : '' ) . ( $rackora_cover ? ' has-cover' : '' );
?>
<article <?php echo get_block_wrapper_attributes( array( 'class' => $rackora_classes ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $rackora_cover ) : ?>
		<a class="post-cover" href="<?php echo esc_url( $rackora_link ); ?>" tabindex="-1" aria-hidden="true">
			<?php
			echo get_the_post_thumbnail(
				$rackora_post,
				'medium_large',
				array(
					'loading'  => 'lazy',
					'decoding' => 'async',
				)
			);
			?>
		</a>
	<?php endif; ?>
	<div class="post-entry__body">
		<div class="post-entry__eyebrow">
			<?php if ( $rackora_pinned ) : ?>
				<span class="status-label"><?php esc_html_e( 'Pinned', 'rackora' ); ?></span>
			<?php endif; ?>
			<?php if ( $rackora_category ) : ?>
				<a class="category-link" href="<?php echo esc_url( get_category_link( $rackora_category ) ); ?>"><?php echo esc_html( $rackora_category->name ); ?></a>
			<?php endif; ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $rackora_post ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d', $rackora_post ) ); ?></time>
		</div>
		<h2 class="post-entry__title"><a href="<?php echo esc_url( $rackora_link ); ?>"><?php echo wp_kses_post( $rackora_title ); ?></a></h2>
		<p class="post-entry__excerpt"><?php echo esc_html( wp_strip_all_tags( get_the_excerpt( $rackora_post ) ) ); ?></p>
		<div class="post-entry__meta">
			<?php if ( rackora_views_trackable( $rackora_post->ID ) ) : ?>
				<span><?php echo esc_html( rackora_views_label( rackora_get_views( $rackora_post->ID ) ) ); ?></span>
			<?php endif; ?>
			<span><?php echo esc_html( rackora_comments_label( (int) get_comments_number( $rackora_post ) ) ); ?></span>
			<?php if ( rackora_is_modified( $rackora_post ) ) : ?>
				<span>
					<?php
					/* translators: %s: modified date. */
					echo esc_html( sprintf( __( 'Updated %s', 'rackora' ), get_the_modified_date( 'm-d', $rackora_post ) ) );
					?>
				</span>
			<?php endif; ?>
		</div>
	</div>
</article>
