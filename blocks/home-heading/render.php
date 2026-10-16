<?php
/**
 * 首页标题区：眉题、站点标题（页面唯一 H1）、副标题、文章总数与短句。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

$rackora_eyebrow  = trim( (string) $attributes['eyebrow'] );
$rackora_tagline  = trim( (string) $attributes['tagline'] );
$rackora_subtitle = get_bloginfo( 'description', 'display' );
$rackora_total    = (int) wp_count_posts( 'post' )->publish;
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'home-heading', 'aria-labelledby' => 'home-title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<p class="home-heading__brand"><?php echo esc_html( '' !== $rackora_eyebrow ? $rackora_eyebrow : __( 'Independent publishing', 'rackora' ) ); ?></p>
	<h1 id="home-title"><?php echo esc_html( get_bloginfo( 'name', 'display' ) ); ?></h1>
	<?php if ( '' !== $rackora_subtitle ) : ?>
		<p><?php echo esc_html( $rackora_subtitle ); ?></p>
	<?php endif; ?>
	<div class="home-heading__meta">
		<span>
			<?php
			/* translators: %s: number of published posts. */
			echo esc_html( sprintf( _n( '%s post', '%s posts', $rackora_total, 'rackora' ), number_format_i18n( $rackora_total ) ) );
			?>
		</span>
		<span><?php echo esc_html( '' !== $rackora_tagline ? $rackora_tagline : __( 'Written for clarity and longevity', 'rackora' ) ); ?></span>
	</div>
</section>
