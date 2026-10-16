<?php
/**
 * 文章目录容器：由前台脚本读取正文 H2 / H3 生成，标题少于两个时保持隐藏。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_singular() ) {
	return;
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'article-sidebar__section', 'data-toc-container' => '', 'hidden' => 'hidden' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="article-sidebar__heading"><h2><?php esc_html_e( 'On this page', 'rackora' ); ?></h2></header>
	<nav data-toc aria-label="<?php esc_attr_e( 'Table of contents', 'rackora' ); ?>"></nav>
</section>
