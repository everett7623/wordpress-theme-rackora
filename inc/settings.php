<?php
/**
 * 主题设置页（外观 → Rackora）。
 *
 * 配色、字体与版宽属于「样式」，页面结构属于站点编辑器；
 * 这里只放 WordPress 没有原生位置的站点级选项：默认配色、备案、内容默认值、SEO 与统计。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 检测会输出 canonical / Open Graph / JSON-LD 的 SEO 插件。
 *
 * @return string 插件名称，未检测到时为空字符串。
 */
function rackora_detected_seo_plugin() {
	$by_file = array(
		'wordpress-seo/wp-seo.php'             => 'Yoast SEO',
		'seo-by-rank-math/rank-math.php'       => 'Rank Math',
		'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
		'all-in-one-seo-pack-pro/all_in_one_seo_pack.php' => 'All in One SEO',
		'wp-seopress/seopress.php'             => 'SEOPress',
		'autodescription/autodescription.php'  => 'The SEO Framework',
		'slim-seo/slim-seo.php'                => 'Slim SEO',
		'squirrly-seo/squirrly.php'            => 'Squirrly SEO',
	);

	if ( ! function_exists( 'is_plugin_active' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	foreach ( $by_file as $file => $name ) {
		if ( is_plugin_active( $file ) ) {
			return $name;
		}
	}

	// 兼容必须插件 / 非常规加载路径：仅在对应运行时常量已定义时认定。
	$by_constant = array(
		'WPSEO_VERSION'             => 'Yoast SEO',
		'RANK_MATH_VERSION'         => 'Rank Math',
		'AIOSEO_VERSION'            => 'All in One SEO',
		'SEOPRESS_VERSION'          => 'SEOPress',
		'THE_SEO_FRAMEWORK_VERSION' => 'The SEO Framework',
		'SLIM_SEO_VER'              => 'Slim SEO',
		'SQ_VERSION'                => 'Squirrly SEO',
	);

	foreach ( $by_constant as $constant => $name ) {
		if ( defined( $constant ) ) {
			return $name;
		}
	}
	return '';
}

/**
 * 设置字段定义，按分区组织。
 *
 * @return array<string, array>
 */
function rackora_settings_fields() {
	return array(
		'general'    => array(
			'title'  => __( 'General', 'rackora' ),
			'fields' => array(
				'color_scheme'  => array(
					'type'        => 'select',
					'label'       => __( 'Default color scheme', 'rackora' ),
					'options'     => array(
						'auto'  => __( 'Follow system', 'rackora' ),
						'light' => __( 'Light', 'rackora' ),
						'dark'  => __( 'Dark', 'rackora' ),
					),
					'description' => __( 'Visitors can still switch with the header button; their choice is remembered in the browser.', 'rackora' ),
				),
				'views_enabled'    => array(
					'type'        => 'checkbox',
					'label'       => __( 'Count post views', 'rackora' ),
					'description' => __( 'Counts each visitor once per browser session with a lightweight request that works with page caching.', 'rackora' ),
				),
				'views_posts_only' => array(
					'type'        => 'checkbox',
					'label'       => __( 'Count views on posts only', 'rackora' ),
					'description' => __( 'When enabled, pages and other content types are excluded from view counts and the site total.', 'rackora' ),
				),
				'launch_date'      => array(
					'type'        => 'date',
					'label'       => __( 'Site launch date', 'rackora' ),
					'description' => __( 'Used by the Site stats block to show days online when the block itself has no date.', 'rackora' ),
				),
			),
		),
		'compliance' => array(
			'title'  => __( 'Filing', 'rackora' ),
			'fields' => array(
				'icp_number' => array(
					'type'        => 'text',
					'label'       => __( 'ICP filing number', 'rackora' ),
					'description' => __( 'Shown in the footer filing block when the block fields are empty.', 'rackora' ),
				),
				'icp_url'    => array(
					'type'        => 'url',
					'label'       => __( 'ICP filing URL', 'rackora' ),
					'description' => __( 'Defaults to the MIIT filing site.', 'rackora' ),
				),
				'psb_number' => array(
					'type'        => 'text',
					'label'       => __( 'Public security filing number', 'rackora' ),
					'description' => __( 'Optional 公网安备 number for mainland China hosts.', 'rackora' ),
				),
				'psb_url'    => array(
					'type'        => 'url',
					'label'       => __( 'Public security filing URL', 'rackora' ),
					'description' => __( 'Link target for the public security filing number.', 'rackora' ),
				),
			),
		),
		'content'    => array(
			'title'  => __( 'Content defaults', 'rackora' ),
			'fields' => array(
				'license_name'  => array(
					'type'        => 'text',
					'label'       => __( 'Default license name', 'rackora' ),
					'description' => __( 'Used by the author and license notice when the block leaves the name blank.', 'rackora' ),
				),
				'license_url'   => array(
					'type'        => 'url',
					'label'       => __( 'Default license URL', 'rackora' ),
					'description' => __( 'Optional link for the default license name.', 'rackora' ),
				),
				'share_targets' => array(
					'type'        => 'checkboxes',
					'label'       => __( 'Default share targets', 'rackora' ),
					'options'     => rackora_share_targets(),
					'description' => __( 'Applied when a share bar block has no platforms selected. Individual blocks can still override this.', 'rackora' ),
				),
				'share_copy'    => array(
					'type'        => 'checkbox',
					'label'       => __( 'Show copy link by default', 'rackora' ),
					'description' => __( 'Used together with the default share targets when a share bar has no platforms selected.', 'rackora' ),
				),
				'ad_disclosure' => array(
					'type'        => 'textarea',
					'label'       => __( 'Default sponsor disclosure', 'rackora' ),
					'description' => __( 'Shown above sponsor cards when a slot leaves its disclosure empty. Leave blank to use the built-in wording.', 'rackora' ),
				),
			),
		),
		'seo'        => array(
			'title'  => __( 'SEO', 'rackora' ),
			'fields' => array(
				'seo_enabled'         => array(
					'type'        => 'checkbox',
					'label'       => __( 'Output theme SEO tags', 'rackora' ),
					'description' => __( 'Canonical URLs, Open Graph, Twitter Cards and JSON-LD. Turned off automatically when a dedicated SEO plugin is active.', 'rackora' ),
				),
				'seo_description'     => array(
					'type'        => 'textarea',
					'label'       => __( 'Site description', 'rackora' ),
					'description' => __( 'Used as the meta description of the home page and archives. Leave empty to use the tagline.', 'rackora' ),
				),
				'noindex_tags'        => array(
					'type'        => 'checkbox',
					'label'       => __( 'Do not index tag pages', 'rackora' ),
					'description' => __( 'Outputs noindex,follow on tag archives and the tag index page. Each tag can override this on its edit screen.', 'rackora' ),
				),
				'noindex_archives'    => array(
					'type'        => 'checkbox',
					'label'       => __( 'Do not index date archives', 'rackora' ),
					'description' => __( 'Outputs noindex,follow on date archives and the Archives page. Posts and categories are not affected.', 'rackora' ),
				),
				'google_verification' => array(
					'type'        => 'text',
					'label'       => __( 'Google verification code', 'rackora' ),
					'description' => __( 'Only the content value of google-site-verification, not the whole meta tag.', 'rackora' ),
				),
				'bing_verification'   => array(
					'type'        => 'text',
					'label'       => __( 'Bing verification code', 'rackora' ),
					'description' => __( 'Only the content value of msvalidate.01.', 'rackora' ),
				),
				'baidu_verification'  => array(
					'type'        => 'text',
					'label'       => __( 'Baidu verification code', 'rackora' ),
					'description' => __( 'Only the content value of baidu-site-verification.', 'rackora' ),
				),
				'organization_name'   => array(
					'type'        => 'text',
					'label'       => __( 'Organization name', 'rackora' ),
					'description' => __( 'Used in Organization and WebSite structured data. Leave empty to use the site title.', 'rackora' ),
				),
				'organization_logo'   => array(
					'type'        => 'image',
					'label'       => __( 'Organization logo', 'rackora' ),
					'description' => __( 'A clear square image for structured data. Leave empty to use the site logo or the Rackora mark.', 'rackora' ),
				),
				'about_url'           => array(
					'type'        => 'url',
					'label'       => __( 'About page URL', 'rackora' ),
					'description' => __( 'Optional, adds context to the Organization entity.', 'rackora' ),
				),
				'social_profiles'     => array(
					'type'        => 'social_profiles',
					'label'       => __( 'Social profile URLs', 'rackora' ),
					'description' => __( 'Filled platforms are output as Organization sameAs. Front-end icons are still edited in the Site Editor social links block.', 'rackora' ),
				),
				'social_other'        => array(
					'type'        => 'textarea',
					'label'       => __( 'Other profile URLs', 'rackora' ),
					'description' => __( 'One URL per line for profiles that are not in the list above.', 'rackora' ),
				),
			),
		),
		'analytics'  => array(
			'title'  => __( 'Analytics', 'rackora' ),
			'fields' => array(
				'analytics_enabled'           => array(
					'type'        => 'checkbox',
					'label'       => __( 'Enable third-party analytics', 'rackora' ),
					'description' => __( 'Off by default. Only services with a valid ID are loaded. Turn this off if an SEO or analytics plugin already injects the same tracker.', 'rackora' ),
				),
				'analytics_exclude_logged_in' => array(
					'type'        => 'checkbox',
					'label'       => __( 'Exclude logged-in users from analytics', 'rackora' ),
					'description' => __( 'Skip third-party trackers for anyone who is signed in, including administrators.', 'rackora' ),
				),
				'ga4_id'             => array(
					'type'        => 'text',
					'label'       => __( 'GA4 Measurement ID', 'rackora' ),
					'description' => __( 'Format G-XXXXXXXXXX. Do not paste the whole script.', 'rackora' ),
				),
				'clarity_id'         => array(
					'type'        => 'text',
					'label'       => __( 'Microsoft Clarity project ID', 'rackora' ),
					'description' => __( 'Only the project ID. Inform visitors about cookies as required in your region.', 'rackora' ),
				),
				'baidu_analytics_id' => array(
					'type'        => 'text',
					'label'       => __( 'Baidu Analytics site ID', 'rackora' ),
					'description' => __( 'The 32 hexadecimal characters after hm.js?.', 'rackora' ),
				),
			),
		),
	);
}

/**
 * 注册设置项、分区与字段。
 */
function rackora_register_settings() {
	register_setting(
		'rackora_settings',
		'rackora_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'rackora_sanitize_settings',
			'default'           => rackora_settings_defaults(),
		)
	);

	foreach ( rackora_settings_fields() as $section_id => $section ) {
		add_settings_section( 'rackora_' . $section_id, $section['title'], '__return_false', 'rackora-settings' );

		foreach ( $section['fields'] as $key => $field ) {
			add_settings_field(
				'rackora_' . $key,
				$field['label'],
				'rackora_render_settings_field',
				'rackora-settings',
				'rackora_' . $section_id,
				array_merge(
					$field,
					array(
						'key'       => $key,
						'label_for' => in_array( $field['type'], array( 'checkbox', 'checkboxes', 'social_profiles' ), true ) ? null : 'rackora-' . $key,
					)
				)
			);
		}
	}
}
add_action( 'admin_init', 'rackora_register_settings' );

/**
 * 校验并清洗提交的设置。
 *
 * @param mixed $input 提交的原始值。
 * @return array<string, mixed>
 */
function rackora_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = rackora_settings_defaults();
	$clean    = array();

	foreach ( rackora_settings_fields() as $section ) {
		foreach ( $section['fields'] as $key => $field ) {
			$value = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';

			switch ( $field['type'] ) {
				case 'checkbox':
					$clean[ $key ] = ! empty( $value );
					break;
				case 'checkboxes':
					// share_targets：用 hidden 标记区分「未出现在表单」与「显式全不选」。
					if ( 'share_targets' === $key && empty( $input['share_targets_submitted'] ) ) {
						$previous      = rackora_option( $key );
						$clean[ $key ] = is_array( $previous ) ? $previous : $defaults[ $key ];
						break;
					}
					$selected      = is_array( $value ) ? $value : array();
					$clean[ $key ] = array_values(
						array_filter(
							$selected,
							static function ( $item ) use ( $field ) {
								return array_key_exists( $item, $field['options'] );
							}
						)
					);
					break;
				case 'select':
					$clean[ $key ] = array_key_exists( $value, $field['options'] ) ? $value : $defaults[ $key ];
					break;
				case 'url':
				case 'image':
					$clean[ $key ] = esc_url_raw( trim( (string) $value ) );
					break;
				case 'textarea':
					$clean[ $key ] = sanitize_textarea_field( (string) $value );
					break;
				case 'date':
					$value = trim( (string) $value );
					if ( '' !== $value && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ) {
						add_settings_error( 'rackora_settings', $key, __( 'The site launch date must use YYYY-MM-DD.', 'rackora' ) );
						$value = '';
					}
					$clean[ $key ] = $value;
					break;
				case 'social_profiles':
					$profiles = is_array( $value ) ? $value : array();
					$clean[ $key ] = array();
					foreach ( array_keys( rackora_social_platforms() ) as $platform ) {
						$url = isset( $profiles[ $platform ] ) ? trim( (string) $profiles[ $platform ] ) : '';
						$clean[ $key ][ $platform ] = '' !== $url ? esc_url_raw( $url ) : '';
					}
					break;
				default:
					$clean[ $key ] = sanitize_text_field( (string) $value );
			}
		}
	}

	// 只保留完整的 http(s) 地址；esc_url_raw 会给普通文本补上 http://，不能单独用来判断有效性。
	$other_lines = array_filter(
		array_map( 'trim', preg_split( '/\r\n|\r|\n/', $clean['social_other'] ) ),
		static function ( $line ) {
			return (bool) preg_match( '#^https?://[^\s/]+#i', $line );
		}
	);
	$clean['social_other'] = implode( "\n", array_filter( array_map( 'esc_url_raw', $other_lines ) ) );

	foreach ( $clean['social_profiles'] as $platform => $url ) {
		if ( '' === $url ) {
			continue;
		}
		if ( 'email' === $platform && 0 === stripos( $url, 'mailto:' ) ) {
			$clean['social_profiles'][ $platform ] = '';
			continue;
		}
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$clean['social_profiles'][ $platform ] = '';
		}
	}

	$patterns = array(
		'ga4_id'             => '/^G-[A-Z0-9]+$/',
		'clarity_id'         => '/^[A-Za-z0-9]+$/',
		'baidu_analytics_id' => '/^[a-fA-F0-9]{32}$/',
	);
	foreach ( $patterns as $key => $pattern ) {
		if ( 'ga4_id' === $key ) {
			$clean[ $key ] = strtoupper( $clean[ $key ] );
		}
		if ( '' !== $clean[ $key ] && ! preg_match( $pattern, $clean[ $key ] ) ) {
			/* translators: %s: setting label. */
			add_settings_error( 'rackora_settings', $key, sprintf( __( '%s has an invalid format and was not saved.', 'rackora' ), rackora_settings_fields()['analytics']['fields'][ $key ]['label'] ) );
			$clean[ $key ] = '';
		}
	}

	return $clean;
}

/**
 * 读取社交预设槽的当前值，必要时从旧版 same_as 迁移展示。
 *
 * @return array<string, string>
 */
function rackora_social_profiles_for_form() {
	$profiles = rackora_option( 'social_profiles' );
	if ( ! is_array( $profiles ) ) {
		$profiles = array();
	}

	$result = array();
	foreach ( array_keys( rackora_social_platforms() ) as $key ) {
		$result[ $key ] = isset( $profiles[ $key ] ) ? (string) $profiles[ $key ] : '';
	}

	$has_any = (bool) array_filter( $result );
	$other   = trim( (string) rackora_option( 'social_other' ) );
	if ( $has_any || '' !== $other ) {
		return $result;
	}

	$saved = get_option( 'rackora_settings', array() );
	if ( ! is_array( $saved ) || empty( $saved['same_as'] ) ) {
		return $result;
	}

	foreach ( preg_split( '/\r\n|\r|\n/', (string) $saved['same_as'] ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$platform = rackora_guess_social_platform( $line );
		if ( '' !== $platform && '' === $result[ $platform ] ) {
			$result[ $platform ] = $line;
		}
	}

	return $result;
}

/**
 * 旧版 same_as 中未能归入预设平台的地址，供「其他」框展示。
 *
 * @return string
 */
function rackora_social_other_for_form() {
	$other = trim( (string) rackora_option( 'social_other' ) );
	if ( '' !== $other ) {
		return $other;
	}

	$profiles = rackora_option( 'social_profiles' );
	$has_any  = is_array( $profiles ) && (bool) array_filter( $profiles );
	if ( $has_any ) {
		return '';
	}

	$saved = get_option( 'rackora_settings', array() );
	if ( ! is_array( $saved ) || empty( $saved['same_as'] ) ) {
		return '';
	}

	$leftover = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $saved['same_as'] ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( '' === rackora_guess_social_platform( $line ) ) {
			$leftover[] = $line;
		}
	}

	return implode( "\n", $leftover );
}

/**
 * 渲染单个设置字段。
 *
 * @param array $args 字段定义。
 */
function rackora_render_settings_field( $args ) {
	$key   = $args['key'];
	$id    = 'rackora-' . $key;
	$name  = 'rackora_settings[' . $key . ']';
	$value = rackora_option( $key );

	switch ( $args['type'] ) {
		case 'checkbox':
			printf(
				'<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s /> %4$s</label>',
				esc_attr( $id ),
				esc_attr( $name ),
				checked( (bool) $value, true, false ),
				esc_html( $args['label'] )
			);
			break;
		case 'checkboxes':
			$selected = is_array( $value ) ? $value : array();
			if ( 'share_targets' === $key ) {
				echo '<input type="hidden" name="rackora_settings[share_targets_submitted]" value="1" />';
			}
			echo '<fieldset class="rackora-checkboxes"><legend class="screen-reader-text">' . esc_html( $args['label'] ) . '</legend>';
			foreach ( $args['options'] as $option => $label ) {
				$option_id = $id . '-' . $option;
				printf(
					'<label for="%1$s" style="display:inline-block;margin:0 1em 0.5em 0;"><input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s" %4$s /> %5$s</label>',
					esc_attr( $option_id ),
					esc_attr( $name ),
					esc_attr( $option ),
					checked( in_array( $option, $selected, true ), true, false ),
					esc_html( $label )
				);
			}
			echo '</fieldset>';
			break;
		case 'select':
			printf( '<select id="%1$s" name="%2$s">', esc_attr( $id ), esc_attr( $name ) );
			foreach ( $args['options'] as $option => $label ) {
				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $option ), selected( $value, $option, false ), esc_html( $label ) );
			}
			echo '</select>';
			break;
		case 'textarea':
			if ( 'social_other' === $key ) {
				$value = rackora_social_other_for_form();
			}
			printf(
				'<textarea id="%1$s" name="%2$s" rows="3" class="large-text">%3$s</textarea>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_textarea( (string) $value )
			);
			break;
		case 'social_profiles':
			$profiles = rackora_social_profiles_for_form();
			echo '<div class="rackora-social-profiles" style="display:grid;gap:0.5em;max-width:36rem;">';
			foreach ( rackora_social_platforms() as $platform => $label ) {
				$field_id = $id . '-' . $platform;
				printf(
					'<label for="%1$s" style="display:grid;grid-template-columns:7rem 1fr;gap:0.75rem;align-items:center;"><span>%2$s</span><input type="url" id="%1$s" name="%3$s[%4$s]" value="%5$s" class="regular-text code" placeholder="https://" /></label>',
					esc_attr( $field_id ),
					esc_html( $label ),
					esc_attr( $name ),
					esc_attr( $platform ),
					esc_attr( $profiles[ $platform ] )
				);
			}
			echo '</div>';
			break;
		case 'image':
			printf(
				'<input type="url" id="%1$s" name="%2$s" value="%3$s" class="regular-text code" /> <button type="button" class="button" data-rackora-media="%1$s">%4$s</button>',
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value ),
				esc_html__( 'Choose image', 'rackora' )
			);
			break;
		default:
			$input_type = 'text';
			if ( 'url' === $args['type'] ) {
				$input_type = 'url';
			} elseif ( 'date' === $args['type'] ) {
				$input_type = 'date';
			}
			printf(
				'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="regular-text" />',
				esc_attr( $input_type ),
				esc_attr( $id ),
				esc_attr( $name ),
				esc_attr( (string) $value )
			);
	}

	if ( ! empty( $args['description'] ) ) {
		printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
	}
}

/**
 * 注册「外观 → Rackora」菜单。
 */
function rackora_add_settings_page() {
	$hook = add_theme_page(
		__( 'Rackora settings', 'rackora' ),
		__( 'Rackora', 'rackora' ),
		'edit_theme_options',
		'rackora-settings',
		'rackora_render_settings_page'
	);

	add_action(
		'admin_enqueue_scripts',
		static function ( $current ) use ( $hook ) {
			if ( $current !== $hook ) {
				return;
			}
			wp_enqueue_media();
			wp_add_inline_script(
				'media-editor',
				'document.addEventListener("click",function(e){var b=e.target.closest("[data-rackora-media]");if(!b){return;}e.preventDefault();var input=document.getElementById(b.getAttribute("data-rackora-media"));var frame=wp.media({library:{type:"image"},multiple:false});frame.on("select",function(){input.value=frame.state().get("selection").first().toJSON().url;});frame.open();});'
			);
		}
	);
}
add_action( 'admin_menu', 'rackora_add_settings_page' );

/**
 * 渲染设置页。
 */
function rackora_render_settings_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$seo_plugin = rackora_detected_seo_plugin();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Rackora settings', 'rackora' ); ?></h1>
		<p>
			<?php
			printf(
				/* translators: 1: Site Editor URL, 2: Styles URL. */
				wp_kses_post( __( 'Page layouts, sidebars, the header and the footer are edited in the <a href="%1$s">Site Editor</a>; colors, fonts and widths live under <a href="%2$s">Styles</a>.', 'rackora' ) ),
				esc_url( admin_url( 'site-editor.php' ) ),
				esc_url( admin_url( 'site-editor.php?p=%2Fstyles' ) )
			);
			?>
		</p>
		<?php if ( '' !== $seo_plugin ) : ?>
			<div class="notice notice-info inline">
				<p>
					<?php
					/* translators: %s: SEO plugin name. */
					echo esc_html( sprintf( __( '%s is active, so Rackora leaves canonical, Open Graph, JSON-LD and robots rules to it. Verification codes still apply. Turn off theme analytics if the plugin already installs a tracker.', 'rackora' ), $seo_plugin ) );
					?>
				</p>
			</div>
		<?php endif; ?>
		<?php settings_errors(); ?>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'rackora_settings' );
			do_settings_sections( 'rackora-settings' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}
