<?php
/**
 * 页面级 SEO：自指 canonical（含列表分页）、Open Graph、Twitter Card、JSON-LD、
 * robots 策略与标题格式。检测到专用 SEO 插件时自动让位，避免重复输出。
 *
 * @package Rackora
 */

defined( 'ABSPATH' ) || exit;

/**
 * 主题 SEO 输出是否生效。
 *
 * @return bool
 */
function rackora_seo_active() {
	$active = (bool) rackora_option( 'seo_enabled' ) && '' === rackora_detected_seo_plugin();

	/**
	 * 过滤主题 SEO 输出开关。
	 *
	 * @param bool $active 是否输出 canonical、Open Graph、JSON-LD 与 robots 规则。
	 */
	return (bool) apply_filters( 'rackora_seo_active', $active );
}

/**
 * 主题输出 canonical 时移除核心的单篇 canonical，避免重复。
 */
function rackora_seo_replace_core_canonical() {
	if ( rackora_seo_active() ) {
		remove_action( 'wp_head', 'rel_canonical' );
	}
}
add_action( 'wp', 'rackora_seo_replace_core_canonical' );

/**
 * 标题顺序与 Halo 版一致：「标题 – 站点 – 第 N 页」，首页不追加副标题。
 * 分隔符沿用核心默认，由 wptexturize() 排版为短破折号。
 *
 * @param array $parts 标题组成部分。
 * @return array
 */
function rackora_document_title_parts( $parts ) {
	unset( $parts['tagline'] );

	if ( isset( $parts['page'] ) ) {
		$page = $parts['page'];
		unset( $parts['page'] );
		$parts['page'] = $page;
	}
	return $parts;
}
add_filter( 'document_title_parts', 'rackora_document_title_parts' );

/**
 * 站点级描述：主题设置 → 站点副标题。
 *
 * @return string
 */
function rackora_site_description() {
	$description = (string) rackora_option( 'seo_description' );
	return '' !== $description ? $description : get_bloginfo( 'description', 'display' );
}

/**
 * 站点 Logo 地址（未设置时为空字符串，不回退主题图标）。
 *
 * @return string
 */
function rackora_custom_logo_url() {
	$logo_id = (int) get_theme_mod( 'custom_logo' );
	$url     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	return $url ? $url : '';
}

/**
 * 结构化数据中的组织 Logo：主题设置 → 站点 Logo → 主题默认图标。
 *
 * @return string
 */
function rackora_organization_logo_url() {
	$logo = (string) rackora_option( 'organization_logo' );
	return '' !== $logo ? $logo : rackora_site_logo_url();
}

/**
 * 纯文本摘要，用于 meta description 与结构化数据。
 *
 * @param WP_Post $post 文章。
 * @return string
 */
function rackora_post_description( $post ) {
	$text = has_excerpt( $post ) ? $post->post_excerpt : get_the_excerpt( $post );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
	return html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
}

/**
 * 收集当前路由的 SEO 信息。
 *
 * @return array{base: string, url: string, title: string, description: string, image: string, type: string}
 */
function rackora_seo_context() {
	$paged   = rackora_current_page();
	$context = array(
		'base'        => '',
		'url'         => '',
		'title'       => wp_get_document_title(),
		'description' => rackora_site_description(),
		'image'       => rackora_custom_logo_url(),
		'type'        => 'website',
	);

	if ( is_front_page() && is_home() ) {
		$context['base'] = home_url( '/' );
	} elseif ( is_home() ) {
		$context['base'] = (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_singular() ) {
		$post                   = get_queried_object();
		$context['base']        = (string) wp_get_canonical_url( $post );
		$context['title']       = wp_strip_all_tags( get_the_title( $post ) );
		$context['description'] = rackora_post_description( $post );
		$context['type']        = rackora_is_template_page( 'about' ) ? 'profile' : 'article';

		$thumbnail = get_the_post_thumbnail_url( $post, 'full' );
		if ( $thumbnail ) {
			$context['image'] = $thumbnail;
		}

		// 归档、分类索引等页面模板带有自己的分页。
		if ( is_page() && $paged > 1 ) {
			$context['url'] = rackora_paged_url( (string) get_permalink( $post ), $paged );
		}
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term            = get_queried_object();
		$link            = get_term_link( $term );
		$context['base'] = is_wp_error( $link ) ? '' : $link;

		$description = trim( wp_strip_all_tags( term_description( $term ) ) );
		if ( '' !== $description ) {
			$context['description'] = $description;
		}
	} elseif ( is_author() ) {
		$author          = get_queried_object();
		$context['base'] = get_author_posts_url( $author->ID );
		$context['type'] = 'profile';
		$context['image'] = get_avatar_url( $author->ID, array( 'size' => 512 ) );

		$bio = trim( (string) get_the_author_meta( 'description', $author->ID ) );
		if ( '' !== $bio ) {
			$context['description'] = $bio;
		}
	} elseif ( is_date() ) {
		$year  = (int) get_query_var( 'year' );
		$month = (int) get_query_var( 'monthnum' );
		$day   = (int) get_query_var( 'day' );
		if ( $day ) {
			$context['base'] = get_day_link( $year, $month, $day );
		} elseif ( $month ) {
			$context['base'] = get_month_link( $year, $month );
		} elseif ( $year ) {
			$context['base'] = get_year_link( $year );
		}
	} elseif ( is_post_type_archive() ) {
		$link            = get_post_type_archive_link( get_query_var( 'post_type' ) );
		$context['base'] = $link ? $link : '';
	}

	if ( '' === $context['url'] ) {
		$context['url'] = is_singular() ? $context['base'] : rackora_paged_url( $context['base'], $paged );
	}

	return $context;
}

/**
 * 输出站点验证、地区信号与页面级 SEO 标签。
 */
function rackora_render_seo_head() {
	$verifications = array(
		'google-site-verification' => 'google_verification',
		'msvalidate.01'            => 'bing_verification',
		'baidu-site-verification'  => 'baidu_verification',
	);
	foreach ( $verifications as $meta_name => $key ) {
		$value = (string) rackora_option( $key );
		if ( '' !== $value ) {
			printf( '<meta name="%1$s" content="%2$s" />' . "\n", esc_attr( $meta_name ), esc_attr( $value ) );
		}
	}

	$region = (string) rackora_option( 'site_region' );
	if ( '' !== $region ) {
		printf( '<meta name="geo.region" content="%s" />' . "\n", esc_attr( $region ) );
	}

	if ( ! rackora_seo_active() || is_404() || is_search() ) {
		return;
	}

	$context = rackora_seo_context();
	if ( '' === $context['url'] ) {
		return;
	}

	$tags = array(
		array( 'link', 'canonical', $context['url'] ),
		array( 'property', 'og:type', $context['type'] ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:locale', get_locale() ),
		array( 'property', 'og:title', $context['title'] ),
		array( 'property', 'og:url', $context['url'] ),
		array( 'property', 'og:description', $context['description'] ),
		array( 'property', 'og:image', $context['image'] ),
		array( 'name', 'twitter:card', '' !== $context['image'] ? 'summary_large_image' : 'summary' ),
		array( 'name', 'twitter:title', $context['title'] ),
		array( 'name', 'twitter:description', $context['description'] ),
		array( 'name', 'twitter:image', $context['image'] ),
	);

	if ( is_singular( 'post' ) ) {
		$post   = get_queried_object();
		$tags[] = array( 'property', 'article:published_time', get_post_time( 'c', true, $post ) );
		$tags[] = array( 'property', 'article:modified_time', rackora_is_modified( $post ) ? get_post_modified_time( 'c', true, $post ) : get_post_time( 'c', true, $post ) );
	}

	foreach ( $tags as $tag ) {
		list( $kind, $name, $value ) = $tag;
		if ( '' === (string) $value ) {
			continue;
		}
		if ( 'link' === $kind ) {
			printf( '<link rel="%1$s" href="%2$s" />' . "\n", esc_attr( $name ), esc_url( $value ) );
		} else {
			printf( '<meta %1$s="%2$s" content="%3$s" />' . "\n", esc_attr( $kind ), esc_attr( $name ), esc_attr( $value ) );
		}
	}

	rackora_render_pagination_links( $context['base'] );
	rackora_render_json_ld();
}
add_action( 'wp_head', 'rackora_render_seo_head', 5 );

/**
 * 列表分页的 rel="prev" / rel="next"。
 *
 * @param string $base 第 1 页地址。
 */
function rackora_render_pagination_links( $base ) {
	global $wp_query;

	if ( '' === $base ) {
		return;
	}

	$paged = rackora_current_page();
	$total = 0;

	if ( ! is_singular() ) {
		$total = (int) $wp_query->max_num_pages;
	} elseif ( rackora_is_template_page( 'archives' ) ) {
		$per_page = max( 1, (int) get_option( 'posts_per_page' ) );
		$total    = (int) ceil( (int) wp_count_posts( 'post' )->publish / $per_page );
		$base     = (string) get_permalink( get_queried_object_id() );
	}

	if ( $total < 2 ) {
		return;
	}
	if ( $paged > 1 ) {
		printf( '<link rel="prev" href="%s" />' . "\n", esc_url( rackora_paged_url( $base, $paged - 1 ) ) );
	}
	if ( $paged < $total ) {
		printf( '<link rel="next" href="%s" />' . "\n", esc_url( rackora_paged_url( $base, $paged + 1 ) ) );
	}
}

/**
 * 当前路由的 JSON-LD：首页输出 WebSite 与 Organization，文章输出 BlogPosting / TechArticle
 * 与 BreadcrumbList，页面输出 WebPage / AboutPage。
 */
function rackora_render_json_ld() {
	$site_url = home_url( '/' );
	$org_id   = untrailingslashit( home_url() ) . '#organization';
	$language = get_bloginfo( 'language' );
	$org_name = (string) rackora_option( 'organization_name' );
	$org_name = '' !== $org_name ? $org_name : get_bloginfo( 'name' );
	$logo     = array(
		'@type' => 'ImageObject',
		'url'   => rackora_organization_logo_url(),
	);
	$graphs   = array();

	if ( is_front_page() || is_home() ) {
		$graphs[] = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'url'         => $site_url,
			'description' => rackora_site_description(),
			'inLanguage'  => $language,
			'publisher'   => array( '@id' => $org_id ),
		);

		$about        = (string) rackora_option( 'about_url' );
		$organization = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'Organization',
			'@id'              => $org_id,
			'name'             => $org_name,
			'url'              => $site_url,
			'mainEntityOfPage' => '' !== $about ? $about : $site_url,
			'logo'             => $logo,
		);
		$same_as      = array_values( array_filter( explode( "\n", (string) rackora_option( 'same_as' ) ) ) );
		if ( $same_as ) {
			$organization['sameAs'] = $same_as;
		}
		$graphs[] = $organization;
	}

	if ( is_singular( 'post' ) ) {
		$post      = get_queried_object();
		$url       = (string) get_permalink( $post );
		$thumbnail = get_the_post_thumbnail_url( $post, 'full' );

		$graphs[] = array(
			'@context'         => 'https://schema.org',
			'@type'            => 'single-docs' === get_page_template_slug( $post ) ? 'TechArticle' : 'BlogPosting',
			'@id'              => $url,
			'url'              => $url,
			'mainEntityOfPage' => array(
				'@type' => 'WebPage',
				'@id'   => $url,
			),
			'headline'         => wp_strip_all_tags( get_the_title( $post ) ),
			'description'      => rackora_post_description( $post ),
			'inLanguage'       => $language,
			'datePublished'    => get_post_time( 'c', true, $post ),
			'dateModified'     => rackora_is_modified( $post ) ? get_post_modified_time( 'c', true, $post ) : get_post_time( 'c', true, $post ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
				'url'   => get_author_posts_url( $post->post_author ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'@id'   => $org_id,
				'name'  => $org_name,
				'url'   => $site_url,
				'logo'  => $logo,
			),
			'image'            => $thumbnail ? $thumbnail : rackora_organization_logo_url(),
		);

		$crumbs   = array(
			array( get_bloginfo( 'name' ), $site_url ),
		);
		$category = rackora_primary_category( $post->ID );
		if ( $category ) {
			$crumbs[] = array( $category->name, get_category_link( $category ) );
		}
		$crumbs[] = array( wp_strip_all_tags( get_the_title( $post ) ), $url );

		$items = array();
		foreach ( $crumbs as $index => $crumb ) {
			$items[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $crumb[0],
				'item'     => $crumb[1],
			);
		}
		$graphs[] = array(
			'@context'        => 'https://schema.org',
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	} elseif ( is_page() && ! is_front_page() ) {
		$post     = get_queried_object();
		$url      = (string) get_permalink( $post );
		$graphs[] = array(
			'@context'    => 'https://schema.org',
			'@type'       => rackora_is_template_page( 'about' ) ? 'AboutPage' : 'WebPage',
			'@id'         => $url,
			'url'         => $url,
			'name'        => wp_strip_all_tags( get_the_title( $post ) ),
			'description' => rackora_post_description( $post ),
			'inLanguage'  => $language,
			'isPartOf'    => array(
				'@type' => 'WebSite',
				'name'  => get_bloginfo( 'name' ),
				'url'   => $site_url,
			),
			'publisher'   => array(
				'@type' => 'Organization',
				'@id'   => $org_id,
				'name'  => $org_name,
			),
		);
	}

	foreach ( $graphs as $graph ) {
		wp_print_inline_script_tag(
			wp_json_encode( $graph, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			array( 'type' => 'application/ld+json' )
		);
	}
}

/**
 * robots 策略：标签、日期归档、归档页与 404 输出 noindex,follow。
 *
 * @param array $robots robots 指令。
 * @return array
 */
function rackora_wp_robots( $robots ) {
	if ( ! rackora_seo_active() || ! rackora_should_noindex() ) {
		return $robots;
	}

	$robots['noindex'] = true;
	$robots['follow']  = true;
	return $robots;
}
add_filter( 'wp_robots', 'rackora_wp_robots' );

/**
 * 当前路由是否应禁止索引。
 *
 * @return bool
 */
function rackora_should_noindex() {
	if ( is_404() ) {
		return true;
	}

	if ( is_tag() ) {
		$policy = (string) get_term_meta( get_queried_object_id(), 'rackora_index_policy', true );
		if ( 'index' === $policy || 'noindex' === $policy ) {
			return 'noindex' === $policy;
		}
		return (bool) rackora_option( 'noindex_tags' );
	}

	if ( rackora_is_template_page( 'tags' ) ) {
		return (bool) rackora_option( 'noindex_tags' );
	}

	if ( is_date() || rackora_is_template_page( 'archives' ) ) {
		return (bool) rackora_option( 'noindex_archives' );
	}

	return false;
}
