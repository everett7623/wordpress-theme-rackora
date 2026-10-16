<?php
/**
 * Title: Index
 * Slug: rackora/template-index
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:rackora/archive-heading /-->

<!-- wp:query {"queryId":4,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"className":"post-query"} -->
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

<!-- wp:rackora/pagination /-->
