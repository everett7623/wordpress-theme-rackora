<?php
/**
 * 推广位：披露文案 + 若干推广卡片。没有有效卡片时不输出任何内容。
 *
 * @package Rackora
 * @var array  $attributes 区块属性。
 * @var string $content    已渲染的推广卡片。
 */

defined( 'ABSPATH' ) || exit;

if ( '' === trim( $content ) ) {
	return;
}

$rackora_disclosure = trim( (string) $attributes['disclosure'] );
if ( '' === $rackora_disclosure ) {
	$rackora_disclosure = trim( (string) rackora_option( 'ad_disclosure' ) );
}
if ( '' === $rackora_disclosure ) {
	$rackora_disclosure = __( 'The site may earn a commission from promotional links without affecting editorial judgment or your price.', 'rackora' );
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'ad-slot', 'aria-label' => __( 'Sponsored content', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p class="ad-disclosure"><?php echo esc_html( $rackora_disclosure ); ?></p>
	<ul class="ad-list">
		<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 子区块已在各自的 render.php 中转义。 ?>
	</ul>
</section>
