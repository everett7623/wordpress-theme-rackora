<?php
/**
 * 文章信息：导语（手动摘要）、作者、发布与更新日期、核验日期、阅读时长与阅读数。
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

$rackora_variant = (string) $attributes['variant'];
$rackora_deck    = $attributes['showDeck'] && has_excerpt( $rackora_post ) ? wp_strip_all_tags( $rackora_post->post_excerpt ) : '';

// 关于页没有手动摘要时，用站点副标题作为导语。
if ( 'page' === $rackora_variant && '' === $rackora_deck && $attributes['showDeck'] && rackora_is_template_page( 'about' ) ) {
	$rackora_deck = get_bloginfo( 'description', 'display' );
}

if ( 'page' === $rackora_variant ) {
	if ( '' !== $rackora_deck ) {
		printf( '<div %1$s><p class="article-deck">%2$s</p></div>', get_block_wrapper_attributes(), esc_html( $rackora_deck ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	return;
}

$rackora_is_docs  = 'docs' === $rackora_variant;
$rackora_minutes  = rackora_reading_minutes( $rackora_post );
$rackora_verified = (string) get_post_meta( $rackora_post->ID, '_rackora_verified_at', true );
/* translators: %s: estimated reading time in minutes. */
$rackora_reading = sprintf( _n( '%s min read', '%s min read', $rackora_minutes, 'rackora' ), number_format_i18n( $rackora_minutes ) );
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( '' !== $rackora_deck ) : ?>
		<p class="article-deck"><?php echo esc_html( $rackora_deck ); ?></p>
	<?php endif; ?>
	<div class="article-meta">
		<?php if ( $rackora_is_docs ) : ?>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $rackora_post ) ); ?>"><?php echo esc_html( get_the_date( 'Y-m-d', $rackora_post ) ); ?></time>
			<span><?php echo esc_html( $rackora_reading ); ?></span>
		<?php else : ?>
			<a class="article-author" href="<?php echo esc_url( get_author_posts_url( (int) $rackora_post->post_author ) ); ?>" rel="author"><?php echo esc_html( get_the_author_meta( 'display_name', (int) $rackora_post->post_author ) ); ?></a>
			<time datetime="<?php echo esc_attr( get_the_date( 'c', $rackora_post ) ); ?>">
				<?php
				/* translators: %s: publish date. */
				echo esc_html( sprintf( __( 'Published %s', 'rackora' ), get_the_date( 'Y-m-d', $rackora_post ) ) );
				?>
			</time>
			<?php if ( rackora_is_modified( $rackora_post ) ) : ?>
				<time datetime="<?php echo esc_attr( get_the_modified_date( 'c', $rackora_post ) ); ?>">
					<?php
					/* translators: %s: modified date. */
					echo esc_html( sprintf( __( 'Updated %s', 'rackora' ), get_the_modified_date( 'Y-m-d', $rackora_post ) ) );
					?>
				</time>
			<?php endif; ?>
			<?php if ( '' !== $rackora_verified ) : ?>
				<span>
					<?php
					/* translators: %s: content verification date. */
					echo esc_html( sprintf( __( 'Verified %s', 'rackora' ), $rackora_verified ) );
					?>
				</span>
			<?php endif; ?>
			<span><?php echo esc_html( $rackora_reading ); ?></span>
			<?php if ( rackora_views_trackable( $rackora_post->ID ) ) : ?>
				<span><?php echo esc_html( rackora_views_label( rackora_get_views( $rackora_post->ID ) ) ); ?></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
