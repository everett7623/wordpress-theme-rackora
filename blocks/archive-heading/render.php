<?php
/**
 * 归档标题区：分类、标签、作者、日期、搜索与 Rackora 索引页的唯一 H1 和简介。
 *
 * @package Rackora
 * @var array $attributes 区块属性。
 */

defined( 'ABSPATH' ) || exit;

global $wp_query;

$rackora_variant = (string) $attributes['variant'];
$rackora_total   = (int) $wp_query->found_posts;
$rackora_title   = '';
$rackora_text    = '';
$rackora_eyebrow = '';
$rackora_class   = 'archive-heading';
$rackora_avatar  = '';

if ( 'auto' !== $rackora_variant && is_page() ) {
	$rackora_page  = get_queried_object();
	$rackora_title = get_the_title( $rackora_page );
	$rackora_text  = has_excerpt( $rackora_page ) ? get_the_excerpt( $rackora_page ) : '';

	if ( '' === $rackora_text ) {
		switch ( $rackora_variant ) {
			case 'archives':
				$rackora_count = (int) wp_count_posts( 'post' )->publish;
				/* translators: %s: number of public posts. */
				$rackora_text = sprintf( _n( '%s public post, organized by publish date.', '%s public posts, organized by publish date.', $rackora_count, 'rackora' ), number_format_i18n( $rackora_count ) );
				break;
			case 'categories':
				$rackora_text = __( 'Browse posts by their primary content type.', 'rackora' );
				break;
			case 'tags':
				$rackora_text = __( 'Browse all tags by topic and context.', 'rackora' );
				break;
			case 'links':
				$rackora_text = __( 'Independent sites and creators worth following.', 'rackora' );
				break;
		}
	}

	if ( 'links' === $rackora_variant ) {
		$rackora_class   = 'page-heading';
		$rackora_eyebrow = __( 'Connections', 'rackora' );
	}
} elseif ( is_category() || is_tag() || is_tax() ) {
	$rackora_term  = get_queried_object();
	$rackora_title = single_term_title( '', false );
	$rackora_text  = trim( wp_strip_all_tags( term_description( $rackora_term ) ) );
	if ( '' === $rackora_text ) {
		$rackora_text = rackora_related_count_label( $rackora_total );
	}
} elseif ( is_author() ) {
	$rackora_author = get_queried_object();
	$rackora_class  = 'author-heading';
	$rackora_title  = $rackora_author->display_name;
	$rackora_avatar = get_avatar( $rackora_author->ID, 80, '', $rackora_author->display_name );
	$rackora_text   = (string) get_the_author_meta( 'description', $rackora_author->ID );
} elseif ( is_date() ) {
	$rackora_year  = (int) get_query_var( 'year' );
	$rackora_month = (int) get_query_var( 'monthnum' );
	$rackora_day   = (int) get_query_var( 'day' );
	// 朴素链接使用 ?m=YYYYMMDD。
	$rackora_m = (string) get_query_var( 'm' );
	if ( ! $rackora_year && '' !== $rackora_m ) {
		$rackora_year  = (int) substr( $rackora_m, 0, 4 );
		$rackora_month = (int) substr( $rackora_m, 4, 2 );
		$rackora_day   = (int) substr( $rackora_m, 6, 2 );
	}
	if ( $rackora_day ) {
		$rackora_title = date_i18n( get_option( 'date_format' ), mktime( 0, 0, 0, $rackora_month, $rackora_day, $rackora_year ) );
	} elseif ( $rackora_month ) {
		$rackora_title = date_i18n( _x( 'F Y', 'monthly archives date format', 'rackora' ), mktime( 0, 0, 0, $rackora_month, 1, $rackora_year ) );
	} else {
		$rackora_title = (string) $rackora_year;
	}
	/* translators: %s: number of public posts. */
	$rackora_text = sprintf( _n( '%s public post, organized by publish date.', '%s public posts, organized by publish date.', $rackora_total, 'rackora' ), number_format_i18n( $rackora_total ) );
} elseif ( is_search() ) {
	/* translators: %s: search query. */
	$rackora_title = sprintf( __( 'Search: %s', 'rackora' ), get_search_query() );
	/* translators: %s: number of search results. */
	$rackora_text = sprintf( _n( '%s result', '%s results', $rackora_total, 'rackora' ), number_format_i18n( $rackora_total ) );
} elseif ( is_post_type_archive() ) {
	$rackora_title = post_type_archive_title( '', false );
	$rackora_text  = rackora_related_count_label( $rackora_total );
} elseif ( is_home() && ! is_front_page() ) {
	$rackora_title = get_the_title( (int) get_option( 'page_for_posts' ) );
	$rackora_text  = get_bloginfo( 'description', 'display' );
} else {
	$rackora_title = __( 'Latest posts', 'rackora' );
}

if ( '' === $rackora_title ) {
	return;
}
?>
<header <?php echo get_block_wrapper_attributes( array( 'class' => $rackora_class ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( '' !== $rackora_avatar ) : ?>
		<?php echo $rackora_avatar; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_avatar() 已转义。 ?>
		<div>
	<?php endif; ?>
	<?php if ( '' !== $rackora_eyebrow ) : ?>
		<p class="eyebrow"><?php echo esc_html( $rackora_eyebrow ); ?></p>
	<?php endif; ?>
	<h1><?php echo esc_html( wp_strip_all_tags( $rackora_title ) ); ?></h1>
	<?php if ( '' !== $rackora_text ) : ?>
		<p><?php echo esc_html( $rackora_text ); ?></p>
	<?php endif; ?>
	<?php if ( is_author() ) : ?>
		<p class="author-heading__count">
			<?php
			/* translators: %s: number of public posts. */
			echo esc_html( sprintf( _n( '%s public post', '%s public posts', $rackora_total, 'rackora' ), number_format_i18n( $rackora_total ) ) );
			?>
		</p>
	<?php endif; ?>
	<?php if ( '' !== $rackora_avatar ) : ?>
		</div>
	<?php endif; ?>
</header>
