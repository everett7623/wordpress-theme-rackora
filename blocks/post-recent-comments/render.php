<?php
/**
 * 侧栏中当前文章的最新评论。
 *
 * @package Rackora
 * @var array    $attributes 区块属性。
 * @var WP_Block $block      区块实例。
 */

defined( 'ABSPATH' ) || exit;

$rackora_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
if ( ! $rackora_post_id ) {
	return;
}

$rackora_comments = get_comments(
	array(
		'post_id' => $rackora_post_id,
		'status'  => 'approve',
		'type'    => 'comment',
		'number'  => max( 1, (int) $attributes['count'] ),
	)
);
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'article-sidebar__section' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="article-sidebar__heading"><h2><?php esc_html_e( 'Recent comments', 'rackora' ); ?></h2></header>
	<?php if ( $rackora_comments ) : ?>
		<ul class="article-sidebar__comments">
			<?php foreach ( $rackora_comments as $rackora_comment ) : ?>
				<li>
					<strong><?php echo esc_html( get_comment_author( $rackora_comment ) ); ?></strong>
					<p><?php echo esc_html( wp_html_excerpt( wp_strip_all_tags( $rackora_comment->comment_content ), 96, '…' ) ); ?></p>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<p class="article-sidebar__empty"><?php esc_html_e( 'No comments yet', 'rackora' ); ?></p>
	<?php endif; ?>
</section>
