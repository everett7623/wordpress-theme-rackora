<?php
/**
 * Title: Home
 * Slug: rackora/template-home
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

$rackora_archives_url = rackora_template_page_url( 'archives' );
?>
<!-- wp:group {"className":"home-grid"} -->
<div class="wp-block-group home-grid"><!-- wp:rackora/home-heading /-->

<!-- wp:group {"tagName":"section","className":"content-section"} -->
<section class="wp-block-group content-section"><!-- wp:group {"className":"section-heading"} -->
<div class="wp-block-group section-heading"><!-- wp:heading {"className":"section-heading__title"} -->
<h2 class="wp-block-heading section-heading__title"><?php esc_html_e( 'Latest posts', 'rackora' ); ?></h2>
<!-- /wp:heading -->
<?php if ( '' !== $rackora_archives_url ) : ?>

<!-- wp:paragraph {"className":"text-link"} -->
<p class="text-link"><a href="<?php echo esc_url( $rackora_archives_url ); ?>"><?php esc_html_e( 'View archives', 'rackora' ); ?></a></p>
<!-- /wp:paragraph -->
<?php endif; ?></div>
<!-- /wp:group -->

<!-- wp:query {"queryId":1,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"className":"post-query"} -->
<div class="wp-block-query post-query"><!-- wp:post-template {"className":"post-list"} -->
<!-- wp:rackora/post-card /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:group {"className":"empty-state"} -->
<div class="wp-block-group empty-state"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'No public posts yet', 'rackora' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Your first published post will appear here.', 'rackora' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->

<!-- wp:rackora/pagination /--></section>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"rackora/home-sidebar"} /--></div>
<!-- /wp:group -->
