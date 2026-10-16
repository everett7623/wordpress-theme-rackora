<?php
/**
 * Title: Archive
 * Slug: rackora/template-archive
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:breadcrumbs {"className":"archive-breadcrumbs"} /-->

<!-- wp:rackora/archive-heading /-->

<!-- wp:rackora/featured-entry /-->

<!-- wp:query {"queryId":2,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"className":"post-query"} -->
<div class="wp-block-query post-query"><!-- wp:post-template {"className":"post-list"} -->
<!-- wp:rackora/post-card /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:group {"className":"empty-state"} -->
<div class="wp-block-group empty-state"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'No posts here yet', 'rackora' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Browse another category or return home.', 'rackora' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->

<!-- wp:rackora/pagination /-->
