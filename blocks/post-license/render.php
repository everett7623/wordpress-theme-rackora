<?php
/**
 * 作者与版权声明：文章标题、原文地址、作者、发布日期与许可方式。
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

$rackora_link        = (string) get_permalink( $rackora_post );
$rackora_license     = trim( (string) $attributes['licenseName'] );
$rackora_license_url = trim( (string) $attributes['licenseUrl'] );
if ( '' === $rackora_license ) {
	$rackora_license = trim( (string) rackora_option( 'license_name' ) );
}
if ( '' === $rackora_license_url ) {
	$rackora_license_url = trim( (string) rackora_option( 'license_url' ) );
}
$rackora_license_text = '' !== $rackora_license ? $rackora_license : __( 'All rights reserved', 'rackora' );
?>
<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'article-license', 'aria-label' => __( 'Author and copyright notice', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<div class="article-license__lead">
		<strong><?php echo wp_kses_post( get_the_title( $rackora_post ) ); ?></strong>
		<a href="<?php echo esc_url( $rackora_link ); ?>"><?php echo esc_html( rawurldecode( $rackora_link ) ); ?></a>
	</div>
	<dl>
		<div>
			<dt><?php esc_html_e( 'Author', 'rackora' ); ?></dt>
			<dd><?php echo esc_html( get_the_author_meta( 'display_name', (int) $rackora_post->post_author ) ); ?></dd>
		</div>
		<div>
			<dt><?php esc_html_e( 'Published', 'rackora' ); ?></dt>
			<dd><?php echo esc_html( get_the_date( 'Y-m-d', $rackora_post ) ); ?></dd>
		</div>
		<div>
			<dt><?php esc_html_e( 'License', 'rackora' ); ?></dt>
			<dd>
				<?php if ( '' !== $rackora_license_url ) : ?>
					<a href="<?php echo esc_url( $rackora_license_url ); ?>" target="_blank" rel="license noopener noreferrer"><?php echo esc_html( $rackora_license_text ); ?></a>
				<?php else : ?>
					<?php echo esc_html( $rackora_license_text ); ?>
				<?php endif; ?>
			</dd>
		</div>
	</dl>
	<span class="article-license__mark" aria-hidden="true">©</span>
</aside>
