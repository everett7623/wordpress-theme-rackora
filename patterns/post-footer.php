<?php
/**
 * Title: Post footer
 * Slug: rackora/post-footer
 * Categories: rackora
 * Description: Tags and share bar, series, related posts, the license notice and adjacent posts.
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"tagName":"footer","className":"article-footer"} -->
<footer class="wp-block-group article-footer"><!-- wp:group {"className":"article-footer__meta"} -->
<div class="wp-block-group article-footer__meta"><!-- wp:post-terms {"term":"post_tag","separator":"","className":"article-tags"} /-->

<!-- wp:rackora/share-bar /--></div>
<!-- /wp:group -->

<!-- wp:rackora/post-series /-->

<!-- wp:rackora/related-posts /-->

<!-- wp:rackora/post-license /-->

<!-- wp:pattern {"slug":"rackora/post-navigation"} /--></footer>
<!-- /wp:group -->
