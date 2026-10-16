<?php
/**
 * Title: Search results
 * Slug: rackora/template-search
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:rackora/archive-heading /-->

<!-- wp:search <?php echo serialize_block_attributes( array( 'label' => __( 'Search', 'rackora' ), 'showLabel' => false, 'placeholder' => __( 'Search this site', 'rackora' ), 'buttonText' => __( 'Search', 'rackora' ), 'className' => 'search-page-form' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /-->

<!-- wp:query {"queryId":3,"query":{"perPage":10,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"className":"post-query"} -->
<div class="wp-block-query post-query"><!-- wp:post-template {"className":"post-list"} -->
<!-- wp:rackora/post-card /-->
<!-- /wp:post-template -->

<!-- wp:query-no-results -->
<!-- wp:group {"className":"empty-state"} -->
<div class="wp-block-group empty-state"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e( 'Nothing matched your search', 'rackora' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p><?php esc_html_e( 'Try different keywords, or browse the archives.', 'rackora' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->
<!-- /wp:query-no-results --></div>
<!-- /wp:query -->

<!-- wp:rackora/pagination /-->
