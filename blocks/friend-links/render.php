<?php
/**
 * 友情链接：数据来自「链接」管理器，链接分类即友链分组，可通过 ?group= 筛选。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

$rackora_groups  = get_terms(
	array(
		'taxonomy'   => 'link_category',
		'hide_empty' => true,
	)
);
$rackora_groups  = is_wp_error( $rackora_groups ) ? array() : $rackora_groups;
$rackora_current = isset( $_GET['group'] ) ? sanitize_title( wp_unslash( $_GET['group'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 只读筛选参数。
$rackora_active  = null;

foreach ( $rackora_groups as $rackora_group ) {
	if ( $rackora_group->slug === $rackora_current ) {
		$rackora_active = $rackora_group;
	}
}

$rackora_links = get_bookmarks(
	array(
		'orderby'  => 'name',
		'category' => $rackora_active ? (string) $rackora_active->term_id : '',
	)
);
$rackora_base  = is_singular() ? (string) get_permalink() : '';
?>
<div <?php echo get_block_wrapper_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( count( $rackora_groups ) > 1 ) : ?>
		<nav class="link-groups" aria-label="<?php esc_attr_e( 'Link groups', 'rackora' ); ?>">
			<a href="<?php echo esc_url( $rackora_base ); ?>"<?php echo $rackora_active ? '' : ' class="is-active" aria-current="page"'; ?>><?php esc_html_e( 'All', 'rackora' ); ?></a>
			<?php foreach ( $rackora_groups as $rackora_group ) : ?>
				<?php $rackora_is_active = $rackora_active && $rackora_active->term_id === $rackora_group->term_id; ?>
				<a href="<?php echo esc_url( add_query_arg( 'group', $rackora_group->slug, $rackora_base ) ); ?>"<?php echo $rackora_is_active ? ' class="is-active" aria-current="page"' : ''; ?>><?php echo esc_html( $rackora_group->name ); ?></a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( $rackora_links ) : ?>
		<ul class="friend-grid">
			<?php foreach ( $rackora_links as $rackora_link ) : ?>
				<?php
				$rackora_rel = trim( 'friend noopener noreferrer ' . $rackora_link->link_rel );
				$rackora_rel = implode( ' ', array_unique( preg_split( '/\s+/', $rackora_rel ) ) );
				?>
				<li>
					<a href="<?php echo esc_url( $rackora_link->link_url ); ?>" target="_blank" rel="<?php echo esc_attr( $rackora_rel ); ?>">
						<?php if ( '' !== $rackora_link->link_image ) : ?>
							<img src="<?php echo esc_url( $rackora_link->link_image ); ?>" alt="<?php echo esc_attr( $rackora_link->link_name ); ?>" width="44" height="44" loading="lazy" decoding="async" />
						<?php endif; ?>
						<span>
							<strong><?php echo esc_html( $rackora_link->link_name ); ?></strong>
							<?php if ( '' !== $rackora_link->link_description ) : ?>
								<small><?php echo esc_html( $rackora_link->link_description ); ?></small>
							<?php endif; ?>
						</span>
						<?php echo rackora_icon( 'external-link', 15 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php else : ?>
		<?php echo rackora_empty_state( __( 'No public links yet', 'rackora' ), __( 'Add links under Links → Add New in the dashboard.', 'rackora' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
</div>
