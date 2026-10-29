<?php
/**
 * 分类 / 标签汇总：分类显示简介与子分类，标签显示文章数。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

$rackora_is_tags = 'post_tag' === $attributes['taxonomy'];

$rackora_terms    = array();
$rackora_children = array();

if ( $rackora_is_tags ) {
	$rackora_terms = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
			'orderby'    => 'name',
		)
	);
} else {
	// 一次取回全部分类，在 PHP 中按父子分组，避免每个顶级分类再查一次库。
	$rackora_all = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
			'orderby'    => 'name',
		)
	);
	if ( ! is_wp_error( $rackora_all ) && $rackora_all ) {
		foreach ( $rackora_all as $rackora_term ) {
			if ( 0 === (int) $rackora_term->parent ) {
				$rackora_terms[] = $rackora_term;
			} else {
				$rackora_children[ (int) $rackora_term->parent ][] = $rackora_term;
			}
		}
	}
}

if ( is_wp_error( $rackora_terms ) || ! $rackora_terms ) {
	printf(
		'<div %1$s>%2$s</div>',
		get_block_wrapper_attributes(), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$rackora_is_tags // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			? rackora_empty_state( __( 'No tags yet', 'rackora' ), __( 'Tags will appear after tagged posts are published.', 'rackora' ) )
			: rackora_empty_state( __( 'No categories yet', 'rackora' ), __( 'Categories you create will appear here.', 'rackora' ) )
	);
	return;
}
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $rackora_is_tags ) : ?>
		<ul class="tag-index">
			<?php foreach ( $rackora_terms as $rackora_term ) : ?>
				<li>
					<a href="<?php echo esc_url( get_term_link( $rackora_term ) ); ?>">
						<span><?php echo esc_html( $rackora_term->name ); ?></span>
						<small><?php echo esc_html( number_format_i18n( $rackora_term->count ) ); ?></small>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<ul class="taxonomy-grid">
			<?php foreach ( $rackora_terms as $rackora_term ) : ?>
				<?php
				$rackora_description = trim( wp_strip_all_tags( $rackora_term->description ) );
				$rackora_kids        = isset( $rackora_children[ (int) $rackora_term->term_id ] ) ? $rackora_children[ (int) $rackora_term->term_id ] : array();
				?>
				<li>
					<a href="<?php echo esc_url( get_term_link( $rackora_term ) ); ?>">
						<strong><?php echo esc_html( $rackora_term->name ); ?></strong>
						<span class="taxonomy-grid__count"><?php echo esc_html( number_format_i18n( $rackora_term->count ) ); ?></span>
						<span><?php echo esc_html( '' !== $rackora_description ? $rackora_description : __( 'View every post in this category', 'rackora' ) ); ?></span>
					</a>
					<?php if ( $rackora_kids ) : ?>
						<ul class="taxonomy-children">
							<?php foreach ( $rackora_kids as $rackora_child ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $rackora_child ) ); ?>"><?php echo esc_html( $rackora_child->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
