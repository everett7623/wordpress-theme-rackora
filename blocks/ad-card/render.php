<?php
/**
 * 推广卡片：名称与地址必填，链接自动加 sponsored、nofollow 与安全属性。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

$rackora_name = trim( (string) $attributes['name'] );
$rackora_url  = trim( (string) $attributes['url'] );

if ( '' === $rackora_name || '' === $rackora_url ) {
	return;
}

$rackora_image       = trim( (string) $attributes['image'] );
$rackora_description = trim( (string) $attributes['description'] );
/* translators: %s: sponsor name. */
$rackora_label = sprintf( __( '%s (sponsored link)', 'rackora' ), $rackora_name );
?>
<li <?php echo get_block_wrapper_attributes( array( 'class' => 'ad-card' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<a href="<?php echo esc_url( $rackora_url ); ?>" target="_blank" rel="sponsored nofollow noopener noreferrer" aria-label="<?php echo esc_attr( $rackora_label ); ?>">
		<?php if ( '' !== $rackora_image ) : ?>
			<img src="<?php echo esc_url( $rackora_image ); ?>" alt="<?php echo esc_attr( $rackora_name ); ?>" width="160" height="90" loading="lazy" decoding="async" />
		<?php endif; ?>
		<span class="ad-card__body">
			<small><?php echo esc_html_x( 'Sponsored', 'sponsor card label', 'rackora' ); ?></small>
			<strong><?php echo esc_html( $rackora_name ); ?></strong>
			<?php if ( '' !== $rackora_description ) : ?>
				<span><?php echo esc_html( $rackora_description ); ?></span>
			<?php endif; ?>
		</span>
		<?php echo rackora_icon( 'external-link', 16 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</a>
</li>
