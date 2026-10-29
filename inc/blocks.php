<?php
/**
 * 自定义区块注册、编辑器脚本与核心区块的适配过滤器。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 注册编辑器脚本与 blocks/ 下的全部区块。
 */
function rackora_register_blocks() {
	wp_register_script(
		'rackora-editor',
		get_theme_file_uri( 'assets/js/editor-blocks.js' ),
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-server-side-render', 'wp-data' ),
		RACKORA_VERSION,
		true
	);

	$inspector = array();
	$files     = glob( get_theme_file_path( 'blocks/*/block.json' ) );

	foreach ( $files ? $files : array() as $file ) {
		$block = register_block_type( dirname( $file ) );
		if ( ! $block ) {
			continue;
		}
		// 只把 block.json 中声明的属性交给通用属性面板，排除区块支持自动添加的属性。
		$metadata                  = wp_json_file_decode( $file, array( 'associative' => true ) );
		$inspector[ $block->name ] = isset( $metadata['attributes'] ) ? array_keys( $metadata['attributes'] ) : array();
	}

	wp_add_inline_script(
		'rackora-editor',
		'window.rackoraEditor = ' . wp_json_encode( rackora_editor_data( $inspector ), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG ) . ';',
		'before'
	);
}
add_action( 'init', 'rackora_register_blocks' );

/**
 * 在文章编辑器侧栏加载「Rackora」元数据面板。
 */
function rackora_enqueue_editor_meta_panel() {
	wp_enqueue_script(
		'rackora-editor-meta',
		get_theme_file_uri( 'assets/js/editor-meta.js' ),
		array( 'rackora-editor', 'wp-plugins', 'wp-editor', 'wp-core-data', 'wp-data', 'wp-components', 'wp-element' ),
		RACKORA_VERSION,
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'rackora_enqueue_editor_meta_panel' );

/**
 * 编辑器脚本所需的数据：属性面板字段、选项文案与文章元数据定义。
 *
 * @param array<string, string[]> $inspector 每个区块在属性面板中展示的属性。
 * @return array
 */
function rackora_editor_data( $inspector ) {
	return array(
		'inspector' => $inspector,
		'labels'    => array(
			'eyebrow'        => __( 'Eyebrow', 'rackora' ),
			'tagline'        => __( 'Tagline', 'rackora' ),
			'launchDate'     => __( 'Launch date (YYYY-MM-DD)', 'rackora' ),
			'showCover'      => __( 'Show featured image', 'rackora' ),
			'variant'        => __( 'Variant', 'rackora' ),
			'showDeck'       => __( 'Show manual excerpt as deck', 'rackora' ),
			'useThemeShare'  => __( 'Use theme share defaults', 'rackora' ),
			'showCopy'       => __( 'Show copy link button', 'rackora' ),
			'targets'        => __( 'Share targets', 'rackora' ),
			'count'          => __( 'Number of items', 'rackora' ),
			'layout'         => __( 'Layout', 'rackora' ),
			'title'          => __( 'Title', 'rackora' ),
			'includeCurrent' => __( 'Include the current post', 'rackora' ),
			'licenseName'    => __( 'License name', 'rackora' ),
			'licenseUrl'     => __( 'License URL', 'rackora' ),
			'taxonomy'       => __( 'Taxonomy', 'rackora' ),
			'disclosure'     => __( 'Disclosure text', 'rackora' ),
			'name'           => __( 'Name', 'rackora' ),
			'description'    => __( 'Short description', 'rackora' ),
			'image'          => __( 'Image', 'rackora' ),
			'url'            => __( 'Destination URL', 'rackora' ),
			'icpNumber'      => __( 'ICP filing number', 'rackora' ),
			'icpUrl'         => __( 'ICP filing link', 'rackora' ),
			'psbNumber'      => __( 'Public security filing number', 'rackora' ),
			'psbUrl'         => __( 'Public security filing link', 'rackora' ),
		),
		'options'   => array(
			'variant:auto'       => __( 'Automatic', 'rackora' ),
			'variant:archives'   => __( 'Archives', 'rackora' ),
			'variant:categories' => __( 'All categories', 'rackora' ),
			'variant:tags'       => __( 'All tags', 'rackora' ),
			'variant:links'      => __( 'Links', 'rackora' ),
			'variant:article'    => __( 'Article', 'rackora' ),
			'variant:docs'       => __( 'Documentation', 'rackora' ),
			'variant:page'       => __( 'Page deck only', 'rackora' ),
			'layout:footer'      => __( 'Below the post', 'rackora' ),
			'layout:sidebar'     => __( 'Sidebar list', 'rackora' ),
			'taxonomy:category'  => __( 'Categories', 'rackora' ),
			'taxonomy:post_tag'  => __( 'Tags', 'rackora' ),
		),
		'share'     => rackora_share_targets(),
		'postMeta'  => rackora_post_meta_fields(),
		'i18n'      => array(
			'settings'     => __( 'Settings', 'rackora' ),
			'metaPanel'    => __( 'Rackora', 'rackora' ),
			'frontendOnly' => __( 'Rendered on the front end with live content.', 'rackora' ),
			'chooseImage'  => __( 'Choose image', 'rackora' ),
			'removeImage'  => __( 'Remove image', 'rackora' ),
		),
	);
}

/**
 * 分享平台名称。
 *
 * @return array<string, string>
 */
function rackora_share_targets() {
	return array(
		'twitter'  => __( 'X', 'rackora' ),
		'facebook' => __( 'Facebook', 'rackora' ),
		'linkedin' => __( 'LinkedIn', 'rackora' ),
		'reddit'   => __( 'Reddit', 'rackora' ),
		'telegram' => __( 'Telegram', 'rackora' ),
		'whatsapp' => __( 'WhatsApp', 'rackora' ),
		'weibo'    => __( 'Weibo', 'rackora' ),
		'email'    => __( 'Email', 'rackora' ),
	);
}

/**
 * 在区块插入器中增加 Rackora 分类。
 *
 * @param array $categories 区块分类。
 * @return array
 */
function rackora_block_categories( $categories ) {
	$categories[] = array(
		'slug'  => 'rackora',
		'title' => __( 'Rackora', 'rackora' ),
		'icon'  => null,
	);
	return $categories;
}
add_filter( 'block_categories_all', 'rackora_block_categories' );

/**
 * 评论关闭且没有评论时整体隐藏评论区，包括标题。
 *
 * @param string   $content  区块输出。
 * @param array    $block    解析后的区块。
 * @param WP_Block $instance 区块实例。
 * @return string
 */
function rackora_hide_closed_comments( $content, $block, $instance ) {
	$post_id = isset( $instance->context['postId'] ) ? (int) $instance->context['postId'] : get_the_ID();

	if ( $post_id && ! comments_open( $post_id ) && ! get_comments_number( $post_id ) ) {
		return '';
	}
	return $content;
}
add_filter( 'render_block_core/comments', 'rackora_hide_closed_comments', 10, 3 );

/**
 * 分类区块的文章数从「 (N)」文本包成独立元素，便于右对齐显示。
 *
 * @param string $content 区块输出。
 * @return string
 */
function rackora_wrap_category_counts( $content ) {
	return (string) preg_replace( '#</a>\s*\(([^()<]+)\)#u', '</a><span class="cat-count">$1</span>', $content );
}
add_filter( 'render_block_core/categories', 'rackora_wrap_category_counts' );

/**
 * 分类与标签归档的面包屑中插入「全部分类 / 全部标签」索引页。
 *
 * @param array $items 面包屑项。
 * @return array
 */
function rackora_breadcrumb_index_items( $items ) {
	$template = is_category() ? 'categories' : ( is_tag() ? 'tags' : '' );
	$page_id  = '' !== $template ? rackora_template_page_id( $template ) : 0;

	if ( ! $page_id ) {
		return $items;
	}

	$position = ( isset( $items[0]['url'] ) && trailingslashit( $items[0]['url'] ) === trailingslashit( home_url( '/' ) ) ) ? 1 : 0;
	array_splice(
		$items,
		$position,
		0,
		array(
			array(
				'label' => get_the_title( $page_id ),
				'url'   => get_permalink( $page_id ),
			),
		)
	);
	return $items;
}
add_filter( 'block_core_breadcrumbs_items', 'rackora_breadcrumb_index_items' );

/**
 * 输出主题分页导航（上一页 / 页码 / 下一页）。
 *
 * @param int    $current 当前页。
 * @param int    $total   总页数。
 * @param string $prev    上一页地址。
 * @param string $next    下一页地址。
 * @param string $label   导航的无障碍名称。
 * @return string
 */
function rackora_pagination_markup( $current, $total, $prev, $next, $label = '' ) {
	if ( $total < 2 ) {
		return '';
	}

	$html = sprintf( '<nav class="pagination" aria-label="%s">', esc_attr( '' !== $label ? $label : __( 'Pagination', 'rackora' ) ) );
	if ( '' !== $prev ) {
		$html .= sprintf(
			'<a class="pagination__link" rel="prev" href="%1$s">%2$s<span>%3$s</span></a>',
			esc_url( $prev ),
			rackora_icon( 'arrow-left' ),
			esc_html_x( 'Previous', 'pagination', 'rackora' )
		);
	}
	$html .= sprintf( '<span class="pagination__status">%1$d / %2$d</span>', (int) $current, (int) $total );
	if ( '' !== $next ) {
		$html .= sprintf(
			'<a class="pagination__link" rel="next" href="%1$s"><span>%2$s</span>%3$s</a>',
			esc_url( $next ),
			esc_html_x( 'Next', 'pagination', 'rackora' ),
			rackora_icon( 'arrow-right' )
		);
	}
	return $html . '</nav>';
}
