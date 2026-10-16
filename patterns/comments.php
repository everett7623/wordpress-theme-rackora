<?php
/**
 * Title: Comments
 * Slug: rackora/comments
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:comments {"className":"comments"} -->
<div class="wp-block-comments comments"><!-- wp:heading {"className":"comments__title"} -->
<h2 class="wp-block-heading comments__title"><?php esc_html_e( 'Comments', 'rackora' ); ?></h2>
<!-- /wp:heading -->

<!-- wp:comment-template -->
<!-- wp:group {"className":"comment-item"} -->
<div class="wp-block-group comment-item"><!-- wp:avatar {"size":40,"className":"comment-item__avatar"} /-->

<!-- wp:group {"className":"comment-item__body"} -->
<div class="wp-block-group comment-item__body"><!-- wp:group {"className":"comment-item__meta"} -->
<div class="wp-block-group comment-item__meta"><!-- wp:comment-author-name /-->

<!-- wp:comment-date {"format":"Y-m-d H:i"} /-->

<!-- wp:comment-edit-link /--></div>
<!-- /wp:group -->

<!-- wp:comment-content /-->

<!-- wp:comment-reply-link /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
<!-- /wp:comment-template -->

<!-- wp:comments-pagination -->
<!-- wp:comments-pagination-previous /-->

<!-- wp:comments-pagination-numbers /-->

<!-- wp:comments-pagination-next /-->
<!-- /wp:comments-pagination -->

<!-- wp:post-comments-form /--></div>
<!-- /wp:comments -->
