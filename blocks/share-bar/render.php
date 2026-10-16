<?php
/**
 * 分享栏：按区块设置输出各平台分享链接与复制链接按钮。
 *
 * @package Rackora
 * @var array    $attributes 区块属性。
 * @var WP_Block $block      区块实例。
 */

defined( 'ABSPATH' ) || exit;

$rackora_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$rackora_post    = get_post( $rackora_post_id );
$rackora_targets = array_intersect( (array) $attributes['targets'], array_keys( rackora_share_targets() ) );

if ( ! $rackora_post || ( ! $rackora_targets && ! $attributes['showCopy'] ) ) {
	return;
}

$rackora_url     = (string) get_permalink( $rackora_post );
$rackora_title   = wp_strip_all_tags( get_the_title( $rackora_post ) );
$rackora_e_url   = rawurlencode( $rackora_url );
$rackora_e_title = rawurlencode( $rackora_title );
$rackora_e_msg   = rawurlencode( $rackora_title . "\n" . $rackora_url );
$rackora_names   = rackora_share_targets();

$rackora_links = array(
	'twitter'  => array( 'https://x.com/intent/tweet?url=' . $rackora_e_url . '&text=' . $rackora_e_title, '' ),
	'facebook' => array( 'https://www.facebook.com/sharer/sharer.php?u=' . $rackora_e_url, 'facebook' ),
	'linkedin' => array( 'https://www.linkedin.com/sharing/share-offsite/?url=' . $rackora_e_url, 'linkedin' ),
	'reddit'   => array( 'https://www.reddit.com/submit?url=' . $rackora_e_url . '&title=' . $rackora_e_title, 'messages-square' ),
	'telegram' => array( 'https://t.me/share/url?url=' . $rackora_e_url . '&text=' . $rackora_e_title, 'send' ),
	'whatsapp' => array( 'https://api.whatsapp.com/send?text=' . $rackora_e_msg, 'message-circle' ),
	'weibo'    => array( 'https://service.weibo.com/share/share.php?url=' . $rackora_e_url . '&title=' . $rackora_e_title, 'globe' ),
	'email'    => array( 'mailto:?subject=' . $rackora_e_title . '&body=' . $rackora_e_msg, 'mail' ),
);
?>
<nav <?php echo get_block_wrapper_attributes( array( 'class' => 'share-bar', 'aria-label' => __( 'Share this post', 'rackora' ), 'data-share-bar' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="share-bar__label"><?php esc_html_e( 'Share', 'rackora' ); ?></span>
	<?php foreach ( $rackora_targets as $rackora_target ) : ?>
		<?php
		list( $rackora_href, $rackora_icon_name ) = $rackora_links[ $rackora_target ];
		/* translators: %s: share target such as X or Facebook. */
		$rackora_label = 'email' === $rackora_target ? __( 'Share by email', 'rackora' ) : sprintf( __( 'Share on %s', 'rackora' ), $rackora_names[ $rackora_target ] );
		$rackora_attrs = 'email' === $rackora_target ? '' : ' target="_blank" rel="noopener noreferrer"';
		?>
		<a class="share-bar__button" href="<?php echo esc_url( $rackora_href, array( 'https', 'mailto' ) ); ?>" data-share="<?php echo esc_attr( $rackora_target ); ?>" aria-label="<?php echo esc_attr( $rackora_label ); ?>" title="<?php echo esc_attr( $rackora_label ); ?>"<?php echo $rackora_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 固定属性字符串。 ?>>
			<?php if ( '' === $rackora_icon_name ) : ?>
				<span class="share-bar__x-mark" aria-hidden="true">X</span>
			<?php else : ?>
				<?php echo rackora_icon( $rackora_icon_name, 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</a>
	<?php endforeach; ?>
	<?php if ( $attributes['showCopy'] ) : ?>
		<button class="share-bar__button" type="button" data-share-link data-share-url="<?php echo esc_url( $rackora_url ); ?>" aria-label="<?php esc_attr_e( 'Copy post link', 'rackora' ); ?>" title="<?php esc_attr_e( 'Copy post link', 'rackora' ); ?>">
			<span class="share-bar__icon share-bar__icon--copy"><?php echo rackora_icon( 'copy', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="share-bar__icon share-bar__icon--check"><?php echo rackora_icon( 'check', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
			<span class="share-bar__icon share-bar__icon--failed"><?php echo rackora_icon( 'x', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
		</button>
		<span class="share-bar__status" role="status" data-share-status></span>
	<?php endif; ?>
</nav>
