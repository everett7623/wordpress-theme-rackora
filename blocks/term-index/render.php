<?php
/**
 * 分类 / 标签汇总：分类显示简介与子分类，标签显示文章数。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

$rackora_is_tags = 'post_tag' === $attributes['taxonomy'];
$rackora_terms   = get_terms(
	array(
		'taxonomy'   => $rackora_is_tags ? 'post_tag' : 'category',
		'hide_empty' => $rackora_is_tags,
		'parent'     => $rackora_is_tags ? '' : 0,
		'orderby'    => 'name',
	)
);

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
				$rackora_children    = get_terms(
					array(
						'taxonomy'   => 'category',
						'parent'     => $rackora_term->term_id,
						'hide_empty' => false,
					)
				);
				?>
				<li>
					<a href="<?php echo esc_url( get_term_link( $rackora_term ) ); ?>">
						<strong><?php echo esc_html( $rackora_term->name ); ?></strong>
						<span class="taxonomy-grid__count"><?php echo esc_html( number_format_i18n( $rackora_term->count ) ); ?></span>
						<span><?php echo esc_html( '' !== $rackora_description ? $rackora_description : __( 'View every post in this category', 'rackora' ) ); ?></span>
					</a>
					<?php if ( ! is_wp_error( $rackora_children ) && $rackora_children ) : ?>
						<ul class="taxonomy-children">
							<?php foreach ( $rackora_children as $rackora_child ) : ?>
								<li><a href="<?php echo esc_url( get_term_link( $rackora_child ) ); ?>"><?php echo esc_html( $rackora_child->name ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
