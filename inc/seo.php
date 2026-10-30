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
	if ( ! rackora_seo_active() ) {
		return $parts;
	}

	unset( $parts['tagline'] );

	if ( is_singular() ) {
		$seo_title = rackora_singular_seo_title( get_queried_object_id() );
		if ( '' !== $seo_title ) {
			$parts['title'] = $seo_title;
		}
	}

	if ( rackora_is_template_page( 'archives' ) && rackora_current_page() > 1 && empty( $parts['page'] ) ) {
		/* translators: %s: page number. */
		$parts['page'] = sprintf( __( 'Page %s', 'rackora' ), number_format_i18n( rackora_current_page() ) );
	}

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
 * 优先读取单篇「SEO 描述」元数据，未填写时回退到摘要。
 *
 * @param WP_Post $post 文章或页面。
 * @return string
 */
function rackora_post_description( $post ) {
	$custom = rackora_singular_seo_description( $post->ID );
	if ( '' !== $custom ) {
		return $custom;
	}

	$text = has_excerpt( $post ) ? $post->post_excerpt : get_the_excerpt( $post );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
	return html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
}

/**
 * 单篇 SEO 标题（编辑器侧栏），未填写时为空字符串。
 *
 * @param int $post_id 内容 ID。
 * @return string
 */
function rackora_singular_seo_title( $post_id ) {
	return trim( (string) get_post_meta( (int) $post_id, '_rackora_seo_title', true ) );
}

/**
 * 单篇 SEO 描述（编辑器侧栏），未填写时为空字符串。
 *
 * @param int $post_id 内容 ID。
 * @return string
 */
function rackora_singular_seo_description( $post_id ) {
	$text = (string) get_post_meta( (int) $post_id, '_rackora_seo_description', true );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', wp_strip_all_tags( $text ) ) );
	return html_entity_decode( $text, ENT_QUOTES, get_bloginfo( 'charset' ) );
}

/**
 * 读取特色图片的替代文本，未填写时回退到页面标题。
 *
 * @param WP_Post $post           文章或页面。
 * @param string  $fallback_title 回退标题。
 * @return string
 */
function rackora_post_thumbnail_alt( $post, $fallback_title ) {
	$thumb_id = (int) get_post_thumbnail_id( $post );
	if ( $thumb_id > 0 ) {
		$alt = trim( (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) );
		if ( '' !== $alt ) {
			return $alt;
		}
	}
	return $fallback_title;
}

/**
 * Open Graph locale：将 WordPress 的 zh_CN 转为 zh-CN。
 *
 * @return string
 */
function rackora_og_locale() {
	return str_replace( '_', '-', get_locale() );
}

/**
 * 收集当前路由的 SEO 信息。
 *
 * @return array{base: string, url: string, title: string, description: string, image: string, image_alt: string, type: string}
 */
function rackora_seo_context() {
	$paged   = rackora_current_page();
	$title   = wp_get_document_title();
	$context = array(
		'base'        => '',
		'url'         => '',
		'title'       => $title,
		'description' => rackora_site_description(),
		'image'       => rackora_custom_logo_url(),
		'image_alt'   => get_bloginfo( 'name', 'display' ),
		'type'        => 'website',
	);

	if ( is_front_page() ) {
		// 博客首页与静态首页都是 website；静态首页同时满足 is_singular()，必须先判断。
		$context['base'] = home_url( '/' );
		$context['type'] = 'website';
		if ( ! is_home() ) {
			$post = get_queried_object();
			if ( $post instanceof WP_Post ) {
				$seo_title              = rackora_singular_seo_title( $post->ID );
				$context['title']       = '' !== $seo_title ? $seo_title : wp_strip_all_tags( get_the_title( $post ) );
				$context['description'] = rackora_post_description( $post );
				$thumbnail              = get_the_post_thumbnail_url( $post, 'full' );
				if ( $thumbnail ) {
					$context['image']     = $thumbnail;
					$context['image_alt'] = rackora_post_thumbnail_alt( $post, $context['title'] );
				}
			}
		}
	} elseif ( is_home() ) {
		$context['base'] = (string) get_permalink( (int) get_option( 'page_for_posts' ) );
	} elseif ( is_singular() ) {
		$post                   = get_queried_object();
		$context['base']        = (string) wp_get_canonical_url( $post );
		$seo_title              = rackora_singular_seo_title( $post->ID );
		$context['title']       = '' !== $seo_title ? $seo_title : wp_strip_all_tags( get_the_title( $post ) );
		$context['description'] = rackora_post_description( $post );
		$context['type']        = rackora_is_template_page( 'about' ) ? 'profile' : 'article';

		$thumbnail = get_the_post_thumbnail_url( $post, 'full' );
		if ( $thumbnail ) {
			$context['image']     = $thumbnail;
			$context['image_alt'] = rackora_post_thumbnail_alt( $post, $context['title'] );
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
		$author               = get_queried_object();
		$context['base']      = get_author_posts_url( $author->ID );
		$context['type']      = 'profile';
		$context['image']     = get_avatar_url( $author->ID, array( 'size' => 512 ) );
		$context['image_alt'] = (string) $author->display_name;

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
 * 输出站点验证与页面级 SEO 标签。
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

	if ( ! rackora_seo_active() || is_404() || is_search() ) {
		return;
	}

	$context = rackora_seo_context();
	if ( '' === $context['url'] ) {
		return;
	}

	$tags = array(
		array( 'name', 'description', $context['description'] ),
		array( 'link', 'canonical', $context['url'] ),
		array( 'property', 'og:type', $context['type'] ),
		array( 'property', 'og:site_name', get_bloginfo( 'name' ) ),
		array( 'property', 'og:locale', rackora_og_locale() ),
		array( 'property', 'og:title', $context['title'] ),
		array( 'property', 'og:url', $context['url'] ),
		array( 'property', 'og:description', $context['description'] ),
		array( 'property', 'og:image', $context['image'] ),
		array( 'property', 'og:image:alt', '' !== $context['image'] ? $context['image_alt'] : '' ),
		array( 'name', 'twitter:card', '' !== $context['image'] ? 'summary_large_image' : 'summary' ),
		array( 'name', 'twitter:title', $context['title'] ),
		array( 'name', 'twitter:description', $context['description'] ),
		array( 'name', 'twitter:image', $context['image'] ),
		array( 'name', 'twitter:image:alt', '' !== $context['image'] ? $context['image_alt'] : '' ),
	);

	if ( is_singular( 'post' ) ) {
		$post     = get_queried_object();
		$category = rackora_primary_category( $post->ID );
		$tags[]   = array( 'property', 'article:published_time', get_post_time( 'c', true, $post ) );
		$tags[]   = array( 'property', 'article:modified_time', rackora_is_modified( $post ) ? get_post_modified_time( 'c', true, $post ) : get_post_time( 'c', true, $post ) );
		if ( $category ) {
			$tags[] = array( 'property', 'article:section', $category->name );
		}
		$post_tags = get_the_tags( $post->ID );
		if ( is_array( $post_tags ) ) {
			foreach ( $post_tags as $post_tag ) {
				$tags[] = array( 'property', 'article:tag', $post_tag->name );
			}
		}
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
		$website = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'WebSite',
			'name'        => get_bloginfo( 'name' ),
			'url'         => $site_url,
			'description' => rackora_site_description(),
			'inLanguage'  => $language,
			'publisher'   => array( '@id' => $org_id ),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => $site_url . '?s={search_term_string}',
				),
				'query-input' => 'required name=search_term_string',
			),
		);
		$graphs[] = $website;

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
		$same_as = rackora_same_as_urls();
		if ( $same_as ) {
			$organization['sameAs'] = $same_as;
		}
		$graphs[] = $organization;
	}

	if ( is_singular( 'post' ) ) {
		$post      = get_queried_object();
		$url       = (string) get_permalink( $post );
		$thumbnail = get_the_post_thumbnail_url( $post, 'full' );
		$category  = rackora_primary_category( $post->ID );
		$words     = rackora_word_count( $post );
		$post_tags = get_the_tags( $post->ID );
		$keywords  = is_array( $post_tags ) ? wp_list_pluck( $post_tags, 'name' ) : array();
		if ( ! $keywords ) {
			$primary_kw = trim( (string) get_post_meta( $post->ID, '_rackora_primary_keyword', true ) );
			if ( '' !== $primary_kw ) {
				$keywords = array( $primary_kw );
			}
		}

		$article = array(
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
		if ( $words > 0 ) {
			$article['wordCount'] = $words;
		}
		if ( $category ) {
			$article['articleSection'] = $category->name;
		}
		if ( $keywords ) {
			$article['keywords'] = array_values( $keywords );
		}
		$graphs[] = $article;

		$crumbs   = array(
			array( get_bloginfo( 'name' ), $site_url ),
		);
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
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$term = get_queried_object();
		$link = get_term_link( $term );
		if ( ! is_wp_error( $link ) ) {
			$term_desc = trim( wp_strip_all_tags( term_description( $term ) ) );
			$graphs[]  = array(
				'@context'    => 'https://schema.org',
				'@type'       => 'CollectionPage',
				'@id'         => $link,
				'url'         => $link,
				'name'        => single_term_title( '', false ),
				'description' => '' !== $term_desc ? $term_desc : rackora_site_description(),
				'inLanguage'  => $language,
				'isPartOf'    => array(
					'@type' => 'WebSite',
					'name'  => get_bloginfo( 'name' ),
					'url'   => $site_url,
				),
			);

			$crumbs    = array( array( get_bloginfo( 'name' ), $site_url ) );
			$index_tpl = is_category() ? 'categories' : ( is_tag() ? 'tags' : '' );
			$index_id  = '' !== $index_tpl ? rackora_template_page_id( $index_tpl ) : 0;
			if ( $index_id ) {
				$crumbs[] = array( wp_strip_all_tags( get_the_title( $index_id ) ), (string) get_permalink( $index_id ) );
			}
			$crumbs[] = array( single_term_title( '', false ), $link );

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
		}
	} elseif ( is_author() ) {
		$author = get_queried_object();
		$url    = get_author_posts_url( $author->ID );
		$bio    = trim( (string) get_the_author_meta( 'description', $author->ID ) );

		$graphs[] = array(
			'@context'    => 'https://schema.org',
			'@type'       => 'ProfilePage',
			'@id'         => $url,
			'url'         => $url,
			'name'        => (string) $author->display_name,
			'description' => '' !== $bio ? $bio : rackora_site_description(),
			'inLanguage'  => $language,
			'mainEntity'  => array(
				'@type' => 'Person',
				'name'  => (string) $author->display_name,
				'url'   => $url,
				'image' => get_avatar_url( $author->ID, array( 'size' => 512 ) ),
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
	if ( is_404() || is_search() ) {
		return true;
	}

	if ( is_singular( array( 'post', 'page' ) ) && 'noindex' === (string) get_post_meta( get_queried_object_id(), '_rackora_robots', true ) ) {
		return true;
	}

	if ( is_tag() || is_category() ) {
		$policy = (string) get_term_meta( get_queried_object_id(), 'rackora_index_policy', true );
		if ( 'index' === $policy || 'noindex' === $policy ) {
			return 'noindex' === $policy;
		}
		if ( is_tag() ) {
			return (bool) rackora_option( 'noindex_tags' );
		}
		return false;
	}

	if ( rackora_is_template_page( 'tags' ) ) {
		return (bool) rackora_option( 'noindex_tags' );
	}

	if ( is_date() || rackora_is_template_page( 'archives' ) ) {
		return (bool) rackora_option( 'noindex_archives' );
	}

	return false;
}

/**
 * robots.txt：补齐 User-agent 分组，并屏蔽站内搜索（搜索页本身也 noindex）。
 *
 * 检测到 SEO 插件时不改 robots.txt，避免与插件规则打架。
 *
 * @param string $output 当前 robots.txt。
 * @param bool   $public 站点是否对搜索引擎公开。
 * @return string
 */
function rackora_robots_txt( $output, $public ) {
	if ( ! $public || ! rackora_seo_active() ) {
		return $output;
	}

	if ( false === stripos( $output, 'User-agent:' ) ) {
		$output = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n" . $output;
	}

	if ( false === stripos( $output, 'Disallow: /?s=' ) ) {
		$output .= "Disallow: /?s=\nDisallow: /search/\n";
	}

	return $output;
}
add_filter( 'robots_txt', 'rackora_robots_txt', -1, 2 );
