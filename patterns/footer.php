<?php
/**
 * Title: Footer
 * Slug: rackora/footer
 * Categories: footer
 * Block Types: core/template-part/footer
 * Inserter: no
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

$rackora_footer_links = array();
foreach ( array( 'archives', 'categories', 'tags', 'links' ) as $rackora_template ) {
	$rackora_page_id = rackora_template_page_id( $rackora_template );
	if ( $rackora_page_id ) {
		$rackora_footer_links[] = array(
			'label' => get_the_title( $rackora_page_id ),
			'url'   => get_permalink( $rackora_page_id ),
		);
	}
}
$rackora_footer_links[] = array(
	'label' => 'RSS',
	'url'   => get_feed_link(),
);

$rackora_credit = sprintf(
	/* translators: 1: WordPress link, 2: theme link, 3: studio link. */
	esc_html__( 'Powered by %1$s · %2$s · %3$s', 'rackora' ),
	'<a href="https://wordpress.org/" rel="noopener" target="_blank">WordPress</a>',
	'<a href="https://github.com/everett7623/wordpress-theme-rackora" rel="noopener" target="_blank">Rackora</a>',
	'<a href="https://everettlabs.dev/" rel="noopener" target="_blank">Everett Labs</a>'
);
?>
<!-- wp:group {"className":"shell footer-inner"} -->
<div class="wp-block-group shell footer-inner"><!-- wp:group {"className":"footer-brand"} -->
<div class="wp-block-group footer-brand"><!-- wp:site-title {"level":0,"isLink":false,"className":"footer-title"} /--></div>
<!-- /wp:group -->

<!-- wp:navigation <?php echo serialize_block_attributes( array( 'overlayMenu' => 'never', 'className' => 'footer-nav', 'ariaLabel' => __( 'Footer navigation', 'rackora' ), 'layout' => array( 'type' => 'flex', 'justifyContent' => 'right' ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> -->
<?php foreach ( $rackora_footer_links as $rackora_link ) : ?>
<!-- wp:navigation-link <?php echo serialize_block_attributes( array( 'label' => $rackora_link['label'], 'url' => $rackora_link['url'], 'kind' => 'custom', 'isTopLevelLink' => true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /-->
<?php endforeach; ?>
<!-- /wp:navigation -->

<!-- wp:paragraph {"className":"footer-credit"} -->
<p class="footer-credit"><?php echo $rackora_credit; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 文案已转义，链接为固定地址。 ?></p>
<!-- /wp:paragraph -->

<!-- wp:rackora/filing /--></div>
<!-- /wp:group -->
