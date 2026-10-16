<?php
/**
 * Title: Adjacent posts
 * Slug: rackora/post-navigation
 * Categories: rackora
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;
?>
<!-- wp:group <?php echo serialize_block_attributes( array( 'className' => 'post-navigation', 'ariaLabel' => __( 'Adjacent posts', 'rackora' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> -->
<div class="wp-block-group post-navigation" aria-label="<?php esc_attr_e( 'Adjacent posts', 'rackora' ); ?>"><!-- wp:post-navigation-link <?php echo serialize_block_attributes( array( 'type' => 'previous', 'label' => _x( 'Previous', 'adjacent post', 'rackora' ), 'showTitle' => true, 'linkLabel' => true, 'className' => 'post-navigation__previous' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /-->

<!-- wp:post-navigation-link <?php echo serialize_block_attributes( array( 'type' => 'next', 'label' => _x( 'Next', 'adjacent post', 'rackora' ), 'showTitle' => true, 'linkLabel' => true, 'className' => 'post-navigation__next' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /--></div>
<!-- /wp:group -->
