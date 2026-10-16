<?php
/**
 * Title: Documentation post
 * Slug: rackora/template-single-docs
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"article-layout article-layout--docs"} -->
<div class="wp-block-group article-layout article-layout--docs"><!-- wp:group {"tagName":"article","className":"article article--docs"} -->
<article class="wp-block-group article article--docs"><!-- wp:group {"tagName":"header","className":"article-header"} -->
<header class="wp-block-group article-header"><!-- wp:paragraph {"className":"eyebrow"} -->
<p class="eyebrow"><?php esc_html_e( 'Documentation', 'rackora' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:post-title {"level":1,"className":"article-title"} /-->

<!-- wp:rackora/post-meta {"variant":"docs"} /--></header>
<!-- /wp:group -->

<!-- wp:post-content {"className":"article-content"} /-->

<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
<footer class="wp-block-group article-footer"><!-- wp:pattern {"slug":"rackora/post-navigation"} /--></footer>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"rackora/comments"} /--></article>
<!-- /wp:group -->

<!-- wp:group <?php echo serialize_block_attributes( array( 'tagName' => 'aside', 'className' => 'article-sidebar', 'ariaLabel' => __( 'Documentation outline', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> -->
<aside class="wp-block-group article-sidebar" aria-label="<?php esc_attr_e( 'Documentation outline', 'rackora' ); ?>"><!-- wp:rackora/post-toc /-->

<!-- wp:rackora/related-posts {"count":8,"layout":"sidebar","includeCurrent":true} /--></aside>
<!-- /wp:group --></div>
<!-- /wp:group -->
