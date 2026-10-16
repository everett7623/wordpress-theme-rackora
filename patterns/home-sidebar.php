<?php
/**
 * Title: Home sidebar
 * Slug: rackora/home-sidebar
 * Categories: rackora
 * Description: Site profile with social links and stats, categories with post counts, and popular tags.
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

$rackora_categories_url = rackora_template_page_url( 'categories' );
$rackora_tags_url       = rackora_template_page_url( 'tags' );
?>
<!-- wp:group <?php echo serialize_block_attributes( array( 'tagName' => 'aside', 'className' => 'home-sidebar', 'ariaLabel' => __( 'Site information', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> -->
<aside class="wp-block-group home-sidebar" aria-label="<?php esc_attr_e( 'Site information', 'rackora' ); ?>"><!-- wp:group {"className":"profile-panel"} -->
<div class="wp-block-group profile-panel"><!-- wp:site-logo {"width":64,"isLink":false,"className":"profile-panel__mark"} /-->

<!-- wp:group {"className":"profile-panel__copy"} -->
<div class="wp-block-group profile-panel__copy"><!-- wp:site-title {"level":2,"isLink":false} /-->

<!-- wp:site-tagline /--></div>
<!-- /wp:group -->

<!-- wp:social-links {"className":"profile-socials"} -->
<ul class="wp-block-social-links profile-socials"><!-- wp:social-link {"url":"","service":"github"} /-->

<!-- wp:social-link {"url":"","service":"x"} /-->

<!-- wp:social-link {"url":"","service":"mail"} /-->

<!-- wp:social-link <?php echo serialize_block_attributes( array( 'url' => get_feed_link(), 'service' => 'feed' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /--></ul>
<!-- /wp:social-links -->

<!-- wp:rackora/site-stats /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"sidebar-panel"} -->
<div class="wp-block-group sidebar-panel"><!-- wp:group {"tagName":"header","className":"sidebar-panel__header"} -->
<header class="wp-block-group sidebar-panel__header"><!-- wp:heading {"className":"sidebar-panel__title"} -->
<h2 class="wp-block-heading sidebar-panel__title"><?php esc_html_e( 'Categories', 'rackora' ); ?></h2>
<!-- /wp:heading -->
<?php if ( '' !== $rackora_categories_url ) : ?>

<!-- wp:paragraph {"className":"sidebar-panel__more"} -->
<p class="sidebar-panel__more"><a href="<?php echo esc_url( $rackora_categories_url ); ?>"><?php esc_html_e( 'All', 'rackora' ); ?></a></p>
<!-- /wp:paragraph -->
<?php endif; ?></header>
<!-- /wp:group -->

<!-- wp:categories {"showPostCounts":true,"className":"sidebar-list"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"className":"sidebar-panel sidebar-panel--tags"} -->
<div class="wp-block-group sidebar-panel sidebar-panel--tags"><!-- wp:group {"tagName":"header","className":"sidebar-panel__header"} -->
<header class="wp-block-group sidebar-panel__header"><!-- wp:heading {"className":"sidebar-panel__title"} -->
<h2 class="wp-block-heading sidebar-panel__title"><?php esc_html_e( 'Tags', 'rackora' ); ?></h2>
<!-- /wp:heading -->
<?php if ( '' !== $rackora_tags_url ) : ?>

<!-- wp:paragraph {"className":"sidebar-panel__more"} -->
<p class="sidebar-panel__more"><a href="<?php echo esc_url( $rackora_tags_url ); ?>"><?php esc_html_e( 'All', 'rackora' ); ?></a></p>
<!-- /wp:paragraph -->
<?php endif; ?></header>
<!-- /wp:group -->

<!-- wp:tag-cloud {"numberOfTags":10,"smallestFontSize":"0.72rem","largestFontSize":"0.72rem","className":"sidebar-tag-list"} /--></div>
<!-- /wp:group --></aside>
<!-- /wp:group -->
