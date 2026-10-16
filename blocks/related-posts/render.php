<?php
/**
 * 相关文章：优先按主分类推荐，无分类时回退到第一个标签。
 *
 * 侧栏布局用于文档模板的「同分类」列表，可包含并高亮当前文章。
 *
 * @package Rackora
 * @var array    $attributes 区块属性。
 * @var WP_Block $block      区块实例。
 */

defined( 'ABSPATH' ) || exit;

$rackora_post_id = isset( $block->context['postId'] ) ? (int) $block->context['postId'] : get_the_ID();
if ( ! $rackora_post_id || 'post' !== get_post_type( $rackora_post_id ) ) {
	return;
}

$rackora_sidebar  = 'sidebar' === $attributes['layout'];
$rackora_count    = max( 1, (int) $attributes['count'] );
$rackora_category = rackora_primary_category( $rackora_post_id );
$rackora_query    = array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => $rackora_count,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
);

if ( ! $attributes['includeCurrent'] ) {
	$rackora_query['post__not_in'] = array( $rackora_post_id );
}

if ( $rackora_category ) {
	$rackora_query['cat'] = $rackora_category->term_id;
} else {
	$rackora_tags = wp_get_post_tags( $rackora_post_id, array( 'fields' => 'ids' ) );
	if ( ! $rackora_tags ) {
		return;
	}
	$rackora_query['tag__in'] = array( (int) $rackora_tags[0] );
}

$rackora_items = get_posts( $rackora_query );
if ( ! $rackora_items ) {
	return;
}

$rackora_title = trim( (string) $attributes['title'] );
if ( '' === $rackora_title ) {
	$rackora_title = $rackora_sidebar ? __( 'In this category', 'rackora' ) : __( 'Related posts', 'rackora' );
}
?>
<?php if ( $rackora_sidebar ) : ?>
	<section <?php echo get_block_wrapper_attributes( array( 'class' => 'article-sidebar__section' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<header class="article-sidebar__heading"><h2><?php echo esc_html( $rackora_title ); ?></h2></header>
		<ol class="article-sidebar__posts">
			<?php foreach ( $rackora_items as $rackora_item ) : ?>
				<?php $rackora_current = (int) $rackora_item->ID === $rackora_post_id; ?>
				<li><a href="<?php echo esc_url( get_permalink( $rackora_item ) ); ?>"<?php echo $rackora_current ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo wp_kses_post( get_the_title( $rackora_item ) ); ?></a></li>
			<?php endforeach; ?>
		</ol>
	</section>
<?php else : ?>
	<aside <?php echo get_block_wrapper_attributes( array( 'class' => 'related-posts', 'aria-label' => $rackora_title ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<h2><?php echo esc_html( $rackora_title ); ?></h2>
		<ol>
			<?php foreach ( $rackora_items as $rackora_item ) : ?>
				<li><a href="<?php echo esc_url( get_permalink( $rackora_item ) ); ?>"><?php echo wp_kses_post( get_the_title( $rackora_item ) ); ?></a></li>
			<?php endforeach; ?>
		</ol>
	</aside>
<?php endif; ?>
