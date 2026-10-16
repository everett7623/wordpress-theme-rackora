<?php
/**
 * Title: Single post
 * Slug: rackora/template-single
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"article-layout"} -->
<div class="wp-block-group article-layout"><!-- wp:group {"tagName":"article","className":"article"} -->
<article class="wp-block-group article"><!-- wp:group {"tagName":"header","className":"article-header"} -->
<header class="wp-block-group article-header"><!-- wp:breadcrumbs {"prefersTaxonomy":true} /-->

<!-- wp:post-title {"level":1,"className":"article-title"} /-->

<!-- wp:rackora/post-meta /--></header>
<!-- /wp:group -->

<!-- wp:rackora/post-disclosure /-->

<!-- wp:post-content {"className":"article-content"} /-->

<!-- wp:pattern {"slug":"rackora/post-footer"} /-->

<!-- wp:pattern {"slug":"rackora/comments"} /--></article>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"rackora/post-sidebar"} /--></div>
<!-- /wp:group -->
