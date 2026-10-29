<?php
/**
 * 文章目录：服务端输出 H2 / H3 大纲，前台脚本负责锚点按钮、滚动高亮与移动端抽屉。
 *
 * @package Rackora
 * @var array    $attributes 区块属性。
 * @var WP_Block $block      区块实例。
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_singular( 'post' ) && ! rackora_is_editor_preview() ) {
	return;
}

$rackora_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
$rackora_outline = rackora_post_heading_outline( $rackora_post_id );

if ( count( $rackora_outline ) < 2 ) {
	return;
}
?>
<section <?php echo get_block_wrapper_attributes( array( 'class' => 'article-sidebar__section', 'data-toc-container' => '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<header class="article-sidebar__heading"><h2><?php esc_html_e( 'On this page', 'rackora' ); ?></h2></header>
	<nav data-toc aria-label="<?php esc_attr_e( 'Table of contents', 'rackora' ); ?>">
		<?php foreach ( $rackora_outline as $rackora_item ) : ?>
			<a href="#<?php echo esc_attr( $rackora_item['id'] ); ?>" data-level="<?php echo esc_attr( (string) $rackora_item['level'] ); ?>"><?php echo esc_html( $rackora_item['text'] ); ?></a>
		<?php endforeach; ?>
	</nav>
</section>
