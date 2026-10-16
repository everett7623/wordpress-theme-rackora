<?php
/**
 * Title: Page not found
 * Slug: rackora/template-404
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

$rackora_archives_url = rackora_template_page_url( 'archives' );
?>
<!-- wp:group {"tagName":"section","className":"error-page"} -->
<section class="wp-block-group error-page"><!-- wp:paragraph {"className":"error-code"} -->
<p class="error-code">404</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":1} -->
<h1 class="wp-block-heading"><?php esc_html_e( 'Page not found', 'rackora' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'This address may have changed, or the content may have been consolidated.', 'rackora' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons {"className":"error-actions"} -->
<div class="wp-block-buttons error-actions"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'rackora' ); ?></a></div>
<!-- /wp:button -->
<?php if ( '' !== $rackora_archives_url ) : ?>

<!-- wp:button {"className":"is-style-outline"} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="<?php echo esc_url( $rackora_archives_url ); ?>"><?php esc_html_e( 'Browse archives', 'rackora' ); ?></a></div>
<!-- /wp:button -->
<?php endif; ?></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->
