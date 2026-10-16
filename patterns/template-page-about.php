<?php
/**
 * Title: About page
 * Slug: rackora/template-page-about
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"article","className":"article article--page article--about"} -->
<article class="wp-block-group article article--page article--about"><!-- wp:group {"tagName":"header","className":"about-heading"} -->
<header class="wp-block-group about-heading"><!-- wp:site-logo {"width":88,"isLink":false,"className":"about-heading__mark"} /-->

<!-- wp:group {"className":"about-heading__copy"} -->
<div class="wp-block-group about-heading__copy"><!-- wp:post-title {"level":1,"className":"article-title"} /-->

<!-- wp:rackora/post-meta {"variant":"page"} /--></div>
<!-- /wp:group -->

<!-- wp:social-links {"className":"profile-socials"} -->
<ul class="wp-block-social-links profile-socials"><!-- wp:social-link {"url":"","service":"github"} /-->

<!-- wp:social-link {"url":"","service":"x"} /-->

<!-- wp:social-link {"url":"","service":"mail"} /-->

<!-- wp:social-link <?php echo serialize_block_attributes( array( 'url' => get_feed_link(), 'service' => 'feed' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /--></ul>
<!-- /wp:social-links --></header>
<!-- /wp:group -->

<!-- wp:post-content {"className":"article-content"} /--></article>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"rackora/comments"} /-->
