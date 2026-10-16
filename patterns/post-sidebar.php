<?php
/**
 * Title: Post sidebar
 * Slug: rackora/post-sidebar
 * Categories: rackora
 * Description: Sticky table of contents and the latest posts, shown beside the article on wide screens.
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group <?php echo serialize_block_attributes( array( 'tagName' => 'aside', 'className' => 'article-sidebar', 'ariaLabel' => __( 'Post navigation', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> -->
<aside class="wp-block-group article-sidebar" aria-label="<?php esc_attr_e( 'Post navigation', 'rackora' ); ?>"><!-- wp:rackora/post-toc /-->

<!-- wp:group {"className":"article-sidebar__section"} -->
<div class="wp-block-group article-sidebar__section"><!-- wp:heading {"className":"article-sidebar__heading"} -->
<h2 class="wp-block-heading article-sidebar__heading"><?php esc_html_e( 'Latest posts', 'rackora' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:latest-posts {"postsToShow":5,"className":"article-sidebar__posts"} /--></div>
<!-- /wp:group --></aside>
<!-- /wp:group -->
