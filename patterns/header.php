<?php
/**
 * Title: Header
 * Slug: rackora/header
 * Categories: header
 * Block Types: core/template-part/header
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group {"className":"shell header-inner"} -->
<div class="wp-block-group shell header-inner"><!-- wp:group {"className":"brand"} -->
<div class="wp-block-group brand"><!-- wp:site-logo {"width":34,"className":"brand-mark"} /-->

<!-- wp:group {"className":"brand-copy"} -->
<div class="wp-block-group brand-copy"><!-- wp:site-title {"level":0,"className":"brand-title"} /-->

<!-- wp:site-tagline {"className":"brand-tagline"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:navigation {"overlayMenu":"mobile","overlayBackgroundColor":"paper","overlayTextColor":"ink","className":"site-nav","layout":{"type":"flex","justifyContent":"center","flexWrap":"nowrap"}} /-->

<!-- wp:group {"className":"header-actions"} -->
<div class="wp-block-group header-actions"><!-- wp:search <?php echo serialize_block_attributes( array( 'label' => __( 'Search', 'rackora' ), 'showLabel' => false, 'placeholder' => __( 'Search this site', 'rackora' ), 'buttonText' => __( 'Search', 'rackora' ), 'buttonPosition' => 'button-only', 'buttonUseIcon' => true, 'className' => 'header-search' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /-->

<!-- wp:rackora/color-scheme-toggle /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
