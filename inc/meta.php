<?php
/**
 * 内容元数据：文章编辑字段（编辑器侧栏面板）、标签索引策略与分类精选文章。
 *
 * 对应 Halo 版 annotation-setting.yaml；标签简介直接使用 WordPress 原生的「描述」字段。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 文章元数据定义。键名以下划线开头，不在「自定义栏目」中重复显示。
 *
 * @return array<string, array{label: string, type: string, help?: string, options?: array<string, string>, default?: string}>
 */
function rackora_post_meta_fields() {
	return array(
		'_rackora_series'             => array(
			'label' => __( 'Series', 'rackora' ),
			'type'  => 'text',
			'help'  => __( 'Use exactly the same name for every post in a series.', 'rackora' ),
		),
		'_rackora_series_order'       => array(
			'label' => __( 'Series order', 'rackora' ),
			'type'  => 'number',
			'help'  => __( 'Optional. Smaller numbers come first.', 'rackora' ),
		),
		'_rackora_affiliate_relation' => array(
			'label'   => __( 'Commercial relationship', 'rackora' ),
			'type'    => 'select',
			'default' => 'none',
			'options' => array(
				'none'             => __( 'None', 'rackora' ),
				'affiliate'        => __( 'Contains affiliate or referral links', 'rackora' ),
				'sponsored_review' => __( 'Vendor-supplied review resources', 'rackora' ),
				'self_purchased'   => __( 'Independently purchased', 'rackora' ),
			),
			'help'    => __( 'Shows a matching disclosure above the post content.', 'rackora' ),
		),
		'_rackora_verified_at'        => array(
			'label' => __( 'Content verified on', 'rackora' ),
			'type'  => 'date',
			'help'  => __( 'Shown in the post meta. Do not update a post only to change this date.', 'rackora' ),
		),
		'_rackora_review_due'         => array(
			'label' => __( 'Next review due', 'rackora' ),
			'type'  => 'date',
		),
		'_rackora_primary_keyword'    => array(
			'label' => __( 'Primary keyword', 'rackora' ),
			'type'  => 'text',
			'help'  => __( 'For editorial review only; it is not output as meta keywords.', 'rackora' ),
		),
		'_rackora_search_intent'      => array(
			'label'   => __( 'Search intent', 'rackora' ),
			'type'    => 'select',
			'options' => array(
				''              => __( 'Not set', 'rackora' ),
				'informational' => __( 'Informational', 'rackora' ),
				'commercial'    => __( 'Commercial investigation', 'rackora' ),
				'transactional' => __( 'Transactional', 'rackora' ),
				'navigational'  => __( 'Navigational', 'rackora' ),
			),
		),
		'_rackora_pillar'             => array(
			'label' => __( 'Pillar', 'rackora' ),
			'type'  => 'text',
			'help'  => __( 'Only fill in an ownership that has been reviewed against your content architecture.', 'rackora' ),
		),
		'_rackora_robots'             => array(
			'label'   => __( 'Search indexing', 'rackora' ),
			'type'    => 'select',
			'default' => '',
			'options' => array(
				''        => __( 'Allow indexing', 'rackora' ),
				'noindex' => __( 'Do not index this post', 'rackora' ),
			),
			'help'    => __( 'Outputs noindex,follow when search engines should skip this post.', 'rackora' ),
		),
	);
}

/**
 * 按字段类型清洗元数据值。
 *
 * @param mixed  $value    原始值。
 * @param string $meta_key 键名。
 * @return string
 */
function rackora_sanitize_post_meta( $value, $meta_key ) {
	$fields = rackora_post_meta_fields();
	$field  = isset( $fields[ $meta_key ] ) ? $fields[ $meta_key ] : array( 'type' => 'text' );
	$value  = sanitize_text_field( (string) $value );

	switch ( $field['type'] ) {
		case 'select':
			return array_key_exists( $value, $field['options'] ) ? $value : (string) ( $field['default'] ?? '' );
		case 'date':
			return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';
		case 'number':
			return is_numeric( $value ) ? (string) (int) $value : '';
		default:
			return $value;
	}
}

/**
 * 注册文章、标签与分类元数据。
 */
function rackora_register_meta() {
	foreach ( rackora_post_meta_fields() as $key => $field ) {
		register_post_meta(
			'post',
			$key,
			array(
				'type'              => 'string',
				'single'            => true,
				'default'           => (string) ( $field['default'] ?? '' ),
				'show_in_rest'      => true,
				'sanitize_callback' => 'rackora_sanitize_post_meta',
				'auth_callback'     => static function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id );
				},
			)
		);
	}

	$index_policy_args = array(
		'type'              => 'string',
		'single'            => true,
		'default'           => 'inherit',
		'sanitize_callback' => static function ( $value ) {
			return in_array( $value, array( 'inherit', 'index', 'noindex' ), true ) ? $value : 'inherit';
		},
	);

	register_term_meta( 'post_tag', 'rackora_index_policy', $index_policy_args );
	register_term_meta( 'category', 'rackora_index_policy', $index_policy_args );

	register_term_meta(
		'category',
		'rackora_featured_post',
		array(
			'type'              => 'integer',
			'single'            => true,
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
}
add_action( 'init', 'rackora_register_meta' );

/**
 * 标签索引策略选项。
 *
 * @return array<string, string>
 */
function rackora_index_policy_options() {
	return array(
		'inherit' => __( 'Follow theme setting', 'rackora' ),
		'index'   => __( 'Allow indexing', 'rackora' ),
		'noindex' => __( 'Do not index', 'rackora' ),
	);
}

/**
 * 输出标签索引策略下拉框。
 *
 * @param string $current 当前值。
 */
function rackora_render_index_policy_select( $current ) {
	wp_nonce_field( 'rackora_term_meta', 'rackora_term_meta_nonce' );
	echo '<select name="rackora_index_policy" id="rackora-index-policy">';
	foreach ( rackora_index_policy_options() as $value => $label ) {
		printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $value ), selected( $current, $value, false ), esc_html( $label ) );
	}
	echo '</select>';
	printf(
		'<p class="description">%s</p>',
		esc_html__( 'For tags, "Follow theme setting" uses Appearance → Rackora. For categories, inherit means allow indexing unless overridden here.', 'rackora' )
	);
}

/**
 * 新建标签表单中的索引策略字段。
 */
function rackora_tag_add_fields() {
	echo '<div class="form-field"><label for="rackora-index-policy">' . esc_html__( 'Index policy', 'rackora' ) . '</label>';
	rackora_render_index_policy_select( 'inherit' );
	echo '</div>';
}
add_action( 'post_tag_add_form_fields', 'rackora_tag_add_fields' );

/**
 * 编辑标签表单中的索引策略字段。
 *
 * @param WP_Term $term 当前标签。
 */
function rackora_tag_edit_fields( $term ) {
	$current = (string) get_term_meta( $term->term_id, 'rackora_index_policy', true );
	echo '<tr class="form-field"><th scope="row"><label for="rackora-index-policy">' . esc_html__( 'Index policy', 'rackora' ) . '</label></th><td>';
	rackora_render_index_policy_select( '' !== $current ? $current : 'inherit' );
	echo '</td></tr>';
}
add_action( 'post_tag_edit_form_fields', 'rackora_tag_edit_fields' );

/**
 * 编辑分类表单中的精选文章字段。
 *
 * @param WP_Term $term 当前分类。
 */
function rackora_category_edit_fields( $term ) {
	$policy  = (string) get_term_meta( $term->term_id, 'rackora_index_policy', true );
	$current = (int) get_term_meta( $term->term_id, 'rackora_featured_post', true );
	$posts   = get_posts(
		array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'cat'            => $term->term_id,
			'no_found_rows'  => true,
		)
	);

	echo '<tr class="form-field"><th scope="row"><label for="rackora-index-policy">' . esc_html__( 'Index policy', 'rackora' ) . '</label></th><td>';
	rackora_render_index_policy_select( '' !== $policy ? $policy : 'inherit' );
	echo '</td></tr>';

	echo '<tr class="form-field"><th scope="row"><label for="rackora-featured-post">' . esc_html__( 'Featured post', 'rackora' ) . '</label></th><td>';
	echo '<select name="rackora_featured_post" id="rackora-featured-post"><option value="0">' . esc_html__( 'None', 'rackora' ) . '</option>';
	foreach ( $posts as $post ) {
		printf(
			'<option value="%1$d" %2$s>%3$s</option>',
			(int) $post->ID,
			selected( $current, (int) $post->ID, false ),
			esc_html( get_the_title( $post ) . ' · ' . get_the_date( 'Y-m-d', $post ) )
		);
	}
	echo '</select>';
	printf( '<p class="description">%s</p>', esc_html__( 'Highlighted at the top of this category archive. Leave as None to hide the entry.', 'rackora' ) );
	echo '</td></tr>';
}
add_action( 'category_edit_form_fields', 'rackora_category_edit_fields' );

/**
 * 保存标签与分类的主题元数据。
 *
 * @param int $term_id 分类法项 ID。
 */
function rackora_save_term_meta( $term_id ) {
	if ( ! isset( $_POST['rackora_term_meta_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['rackora_term_meta_nonce'] ), 'rackora_term_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_term', $term_id ) ) {
		return;
	}

	if ( isset( $_POST['rackora_index_policy'] ) ) {
		update_term_meta( $term_id, 'rackora_index_policy', sanitize_key( $_POST['rackora_index_policy'] ) );
	}
	if ( isset( $_POST['rackora_featured_post'] ) ) {
		update_term_meta( $term_id, 'rackora_featured_post', absint( $_POST['rackora_featured_post'] ) );
	}
}
add_action( 'created_post_tag', 'rackora_save_term_meta' );
add_action( 'edited_post_tag', 'rackora_save_term_meta' );
add_action( 'edited_category', 'rackora_save_term_meta' );
