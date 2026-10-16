<?php
/**
 * 推广关系披露：根据文章元数据「推广关系」显示对应声明。
 *
 * @package Rackora
 * @var WP_Block $block 区块实例。
 */

defined( 'ABSPATH' ) || exit;

$rackora_post_id  = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$rackora_relation = (string) get_post_meta( $rackora_post_id, '_rackora_affiliate_relation', true );
$rackora_messages = array(
	'affiliate'        => __( 'This post contains affiliate or referral links. The site may earn a commission without affecting your price or our editorial judgment.', 'rackora' ),
	'sponsored_review' => __( 'A vendor supplied resources for this review; the methods, results, and limitations remain independently documented.', 'rackora' ),
	'self_purchased'   => __( 'The product or service covered in this post was independently purchased for testing.', 'rackora' ),
);

if ( ! isset( $rackora_messages[ $rackora_relation ] ) ) {
	return;
}
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'article-disclosure', 'aria-label' => __( 'Content relationship disclosure', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p><?php echo esc_html( $rackora_messages[ $rackora_relation ] ); ?></p>
</aside>
