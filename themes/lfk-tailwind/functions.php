<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LFK_TAILWIND_VERSION', '0.1.0' );

function lfk_is_staging_host() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) ) : '';
	$host = preg_replace( '/:\d+$/', '', $host );

	return 'staging.learningforkidz.com' === $host;
}

add_action( 'send_headers', function () {
	if ( lfk_is_staging_host() ) {
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}
} );

add_filter( 'wp_robots', function ( $robots ) {
	if ( ! lfk_is_staging_host() ) {
		return $robots;
	}

	unset( $robots['index'], $robots['follow'] );
	$robots['noindex']  = true;
	$robots['nofollow'] = true;

	return $robots;
} );

add_filter( 'rank_math/frontend/robots', function ( $robots ) {
	if ( ! lfk_is_staging_host() ) {
		return $robots;
	}

	return array(
		'index'  => 'noindex',
		'follow' => 'nofollow',
	);
}, 1000 );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'lfk-tailwind' ),
	) );
} );

add_action( 'elementor/theme/register_locations', function ( $manager ) {
	$manager->register_core_location( 'header' );
	$manager->register_core_location( 'footer' );
}, 1 );

add_action( 'wp_enqueue_scripts', function () {
	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();

	$css_path = $theme_dir . '/assets/dist/theme.css';
	$js_path  = $theme_dir . '/assets/js/theme.js';

	wp_enqueue_style(
		'lfk-anuphan',
		'https://fonts.googleapis.com/css2?family=Anuphan:wght@100;200;300;400;500;600;700&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'lfk-tailwind',
		$theme_uri . '/assets/dist/theme.css',
		array(),
		file_exists( $css_path ) ? filemtime( $css_path ) : LFK_TAILWIND_VERSION
	);

	wp_enqueue_script(
		'lfk-theme',
		$theme_uri . '/assets/js/theme.js',
		array(),
		file_exists( $js_path ) ? filemtime( $js_path ) : LFK_TAILWIND_VERSION,
		true
	);

	wp_localize_script(
		'lfk-theme',
		'lfkSearch',
		array(
			'ajaxUrl'  => add_query_arg( 'lfk_ajax_search', '1', home_url( '/' ) ),
			'minChars' => 2,
		)
	);
} );

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'lfk-theme' !== $handle ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}, 10, 2 );

add_filter( 'style_loader_tag', function ( $html, $handle, $href, $media ) {
	if ( 'lfk-anuphan' !== $handle ) {
		return $html;
	}

	$href = esc_url( $href );

	return '<link rel="preload" as="style" id="lfk-anuphan-preload" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n"
		. '<noscript><link rel="stylesheet" id="lfk-anuphan-css" href="' . $href . '" media="' . esc_attr( $media ) . '"></noscript>' . "\n";
}, 10, 4 );

add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
	if ( 'preconnect' !== $relation_type || ! lfk_should_trim_plugin_assets() ) {
		return $urls;
	}

	$urls[] = array(
		'href'        => 'https://fonts.googleapis.com',
		'crossorigin' => '',
	);
	$urls[] = array(
		'href'        => 'https://fonts.gstatic.com',
		'crossorigin' => '',
	);

	return $urls;
}, 10, 2 );

function lfk_print_image_preload( $image_id, $size, $sizes, $media = '', $extra_attrs = array() ) {
	$image = wp_get_attachment_image_src( $image_id, $size );
	if ( ! $image ) {
		return;
	}

	$srcset       = wp_get_attachment_image_srcset( $image_id, $size );
	$href         = lfk_prefer_local_upload_webp_url( $image[0] );
	$versioned    = lfk_version_local_upload_url( $image[0] );
	$attrs        = array(
		'rel'  => 'preload',
		'as'   => 'image',
		'href' => $href,
	);

	if ( $srcset && $href === $versioned ) {
		$attrs['imagesrcset'] = lfk_version_local_upload_srcset( $srcset );
		$attrs['imagesizes']  = $sizes;
	}

	if ( $media ) {
		$attrs['media'] = $media;
	}

	foreach ( $extra_attrs as $name => $value ) {
		if ( false === $value || null === $value || '' === $value ) {
			continue;
		}
		$attrs[ $name ] = $value;
	}

	echo '<link';
	foreach ( $attrs as $name => $value ) {
		echo ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}
	echo '>' . "\n";
}

function lfk_print_fixed_image_preload( $image_id, $size, $media = '', $extra_attrs = array() ) {
	$image = wp_get_attachment_image_src( $image_id, $size );
	if ( ! $image ) {
		return;
	}

	$attrs = array(
		'rel'  => 'preload',
		'as'   => 'image',
		'href' => lfk_prefer_local_upload_webp_url( $image[0] ),
	);

	if ( $media ) {
		$attrs['media'] = $media;
	}

	foreach ( $extra_attrs as $name => $value ) {
		if ( false === $value || null === $value || '' === $value ) {
			continue;
		}
		$attrs[ $name ] = $value;
	}

	echo '<link';
	foreach ( $attrs as $name => $value ) {
		echo ' ' . esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
	}
	echo '>' . "\n";
}

function lfk_version_local_upload_url( $url ) {
	$uploads = wp_get_upload_dir();
	if ( empty( $uploads['baseurl'] ) || empty( $uploads['basedir'] ) || ! str_starts_with( $url, $uploads['baseurl'] ) ) {
		return $url;
	}

	$relative = ltrim( substr( $url, strlen( $uploads['baseurl'] ) ), '/' );
	$file     = trailingslashit( $uploads['basedir'] ) . $relative;
	if ( ! file_exists( $file ) ) {
		return $url;
	}

	return add_query_arg( 'v', filemtime( $file ), $url );
}

function lfk_version_local_upload_srcset( $srcset ) {
	if ( ! $srcset ) {
		return '';
	}

	$items = array();
	foreach ( explode( ',', $srcset ) as $item ) {
		$parts = preg_split( '/\s+/', trim( $item ) );
		if ( empty( $parts[0] ) ) {
			continue;
		}

		$url      = lfk_version_local_upload_url( $parts[0] );
		$items[] = trim( $url . ' ' . implode( ' ', array_slice( $parts, 1 ) ) );
	}

	return implode( ', ', $items );
}

add_action( 'wp_head', function () {
	if ( is_front_page() ) {
		$front_id = (int) get_option( 'page_on_front' );
		$slides   = function_exists( 'get_field' ) ? get_field( 'hero_banner_slider', $front_id ) : array();
		$first    = is_array( $slides ) ? reset( $slides ) : null;

		if ( is_array( $first ) ) {
			$desktop_img = isset( $first['desktop_image'] ) && is_array( $first['desktop_image'] ) ? $first['desktop_image'] : null;
			$mobile_img  = isset( $first['mobile_image'] ) && is_array( $first['mobile_image'] ) ? $first['mobile_image'] : null;
			$desktop_id  = ! empty( $desktop_img['ID'] ) ? (int) $desktop_img['ID'] : ( ! empty( $desktop_img['id'] ) ? (int) $desktop_img['id'] : 0 );
			$mobile_id   = ! empty( $mobile_img['ID'] ) ? (int) $mobile_img['ID'] : ( ! empty( $mobile_img['id'] ) ? (int) $mobile_img['id'] : 0 );

			if ( $mobile_id ) {
				lfk_print_image_preload( $mobile_id, 'full', '100vw', '(max-width: 767px)' );
			}
			if ( $desktop_id ) {
				lfk_print_image_preload( $desktop_id, 'full', '100vw', '(min-width: 768px)' );
			}
		}
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_queried_object_id() );
		if ( $product && $product->get_image_id() ) {
			lfk_print_image_preload( $product->get_image_id(), 'woocommerce_single', '(max-width: 767px) 370px, 575px' );
		}
	}

	if ( is_tax( 'product_cat' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$children = get_terms( array(
				'taxonomy'   => 'product_cat',
				'parent'     => $term->term_id,
				'hide_empty' => true,
				'orderby'    => 'name',
				'order'      => 'ASC',
			) );
			if ( ! is_wp_error( $children ) ) {
				foreach ( $children as $child ) {
					$thumbnail_id = (int) get_term_meta( $child->term_id, 'thumbnail_id', true );
					if ( $thumbnail_id ) {
						lfk_print_image_preload( $thumbnail_id, 'medium_large', '(max-width: 767px) 164px, 260px', '', array( 'fetchpriority' => 'high' ) );
						break;
					}
				}
			}
		}
	}

	if ( is_tax( 'product_brand' ) && function_exists( 'wc_get_product' ) ) {
		global $wp_query;

		$first_post = isset( $wp_query->posts[0] ) ? $wp_query->posts[0] : null;
		$product_id = $first_post instanceof WP_Post ? (int) $first_post->ID : 0;
		$product    = $product_id ? wc_get_product( $product_id ) : null;
		if ( $product && $product->get_image_id() ) {
			lfk_print_image_preload( $product->get_image_id(), 'woocommerce_thumbnail', '(max-width: 767px) 155px, 260px', '', array( 'fetchpriority' => 'high' ) );
		}
	}

	if ( is_home() && ! is_paged() ) {
		global $wp_query;

		$posts = isset( $wp_query->posts ) && is_array( $wp_query->posts ) ? array_slice( $wp_query->posts, 0, 2 ) : array();
		foreach ( $posts as $post ) {
			$thumbnail_id = get_post_thumbnail_id( $post );
			if ( $thumbnail_id ) {
				lfk_print_fixed_image_preload( $thumbnail_id, 'medium', '', array( 'fetchpriority' => 'high' ) );
			}
		}
	}
}, 1 );

function lfk_theme_template_path( $template_name ) {
	$template = locate_template( $template_name );
	return $template ? $template : '';
}

function lfk_static_page_slugs() {
	return array( 'about-us', 'refund', 'how-to-orders', 'privacy-policy' );
}

function lfk_custom_template_include( $template ) {
	if ( is_admin() ) {
		return $template;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return lfk_theme_template_path( 'page-cart.php' ) ?: $template;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return lfk_theme_template_path( 'page-checkout.php' ) ?: $template;
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		return lfk_theme_template_path( 'page-my-account.php' ) ?: $template;
	}

	if ( is_page( array( 'promotion', 'ages', 'brands', 'contact-us', 'wishlists' ) ) ) {
		$page_template = lfk_theme_template_path( 'page-' . get_post_field( 'post_name', get_queried_object_id() ) . '.php' );
		return $page_template ?: $template;
	}

	if ( is_page( lfk_static_page_slugs() ) ) {
		return lfk_theme_template_path( 'page-static.php' ) ?: $template;
	}

	if ( function_exists( 'is_product' ) && is_product() ) {
		return lfk_theme_template_path( 'single-product.php' ) ?: $template;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return lfk_theme_template_path( 'archive-product.php' ) ?: $template;
	}

	if ( is_tax( 'product_cat' ) ) {
		return lfk_theme_template_path( 'taxonomy-product_cat.php' ) ?: $template;
	}

	if ( is_tax( 'product_tag' ) ) {
		return lfk_theme_template_path( 'taxonomy-product_tag.php' ) ?: $template;
	}

	if ( is_tax( 'product_brand' ) ) {
		return lfk_theme_template_path( 'taxonomy-product_brand.php' ) ?: $template;
	}

	if ( is_tax( 'age' ) ) {
		return lfk_theme_template_path( 'taxonomy-age.php' ) ?: $template;
	}

	$queried_object = get_queried_object();
	if ( $queried_object instanceof WP_Term && str_starts_with( $queried_object->taxonomy, 'pa_' ) ) {
		return lfk_theme_template_path( 'taxonomy-product_attribute.php' ) ?: $template;
	}

	if ( is_post_type_archive( 'product' ) ) {
		return lfk_theme_template_path( 'archive-product.php' ) ?: $template;
	}

	if ( is_front_page() ) {
		return lfk_theme_template_path( 'front-page.php' ) ?: $template;
	}

	if ( is_home() ) {
		return lfk_theme_template_path( 'home.php' ) ?: $template;
	}

	if ( is_singular( 'post' ) ) {
		return lfk_theme_template_path( 'single.php' ) ?: $template;
	}

	if ( is_search() ) {
		return lfk_theme_template_path( 'search.php' ) ?: $template;
	}

	if ( is_archive() ) {
		return lfk_theme_template_path( 'archive.php' ) ?: $template;
	}

	return $template;
}
add_filter( 'template_include', 'lfk_custom_template_include', 10001 );

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
		return;
	}

	$query->set( 'post_type', array( 'product', 'post', 'page' ) );
	$query->set( 'posts_per_page', 12 );
	$query->set( 'no_found_rows', true );
	$query->set( 'ignore_sticky_posts', true );
	$query->set( 'lfk_include_sku', true );
} );

function lfk_search_query_includes_products( $query ) {
	$post_type = $query instanceof WP_Query ? $query->get( 'post_type' ) : '';

	if ( is_array( $post_type ) ) {
		return in_array( 'product', $post_type, true );
	}

	return 'product' === $post_type;
}

function lfk_add_search_product_id( &$product_ids, $product_id, $limit ) {
	if ( count( $product_ids ) >= $limit || ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$product_id = absint( $product_id );
	if ( ! $product_id ) {
		return;
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}

	if ( $product->is_type( 'variation' ) ) {
		$product_id = absint( $product->get_parent_id() );
		$product    = $product_id ? wc_get_product( $product_id ) : null;
	}

	if ( ! $product || 'publish' !== $product->get_status() || in_array( $product_id, $product_ids, true ) ) {
		return;
	}

	$product_ids[] = $product_id;
}

function lfk_product_ids_matching_sku( $term, $limit = 12 ) {
	global $wpdb;

	if ( ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$term = trim( sanitize_text_field( wp_unslash( $term ) ) );
	if ( strlen( $term ) < 2 ) {
		return array();
	}

	$limit       = max( 1, min( 50, absint( $limit ) ) );
	$product_ids = array();

	if ( function_exists( 'wc_get_product_id_by_sku' ) ) {
		lfk_add_search_product_id( $product_ids, wc_get_product_id_by_sku( $term ), $limit );
	}

	$like        = '%' . $wpdb->esc_like( $term ) . '%';
	$prefix_like = $wpdb->esc_like( $term ) . '%';
	$candidates  = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT CASE WHEN posts.post_type = 'product_variation' AND posts.post_parent > 0 THEN posts.post_parent ELSE posts.ID END
			FROM {$wpdb->postmeta} AS meta
			INNER JOIN {$wpdb->posts} AS posts ON posts.ID = meta.post_id
			WHERE meta.meta_key = '_sku'
				AND meta.meta_value LIKE %s
				AND posts.post_type IN ('product', 'product_variation')
				AND posts.post_status = 'publish'
			ORDER BY CASE WHEN meta.meta_value = %s THEN 0 WHEN meta.meta_value LIKE %s THEN 1 ELSE 2 END, posts.menu_order ASC, posts.post_title ASC
			LIMIT %d",
			$like,
			$term,
			$prefix_like,
			$limit * 3
		)
	);

	foreach ( $candidates as $candidate_id ) {
		lfk_add_search_product_id( $product_ids, $candidate_id, $limit );
	}

	return array_slice( $product_ids, 0, $limit );
}

add_action( 'wp_ajax_lfk_ajax_search', 'lfk_ajax_search' );
add_action( 'wp_ajax_nopriv_lfk_ajax_search', 'lfk_ajax_search' );
add_action( 'template_redirect', 'lfk_frontend_ajax_search', 0 );
function lfk_frontend_ajax_search() {
	if ( ! isset( $_GET['lfk_ajax_search'] ) ) {
		return;
	}

	lfk_ajax_search();
}

function lfk_ajax_search() {
	$term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
	$term = trim( $term );

	if ( strlen( $term ) < 2 ) {
		wp_send_json_success( array(
			'results'   => array(),
			'searchUrl' => add_query_arg( 's', $term, home_url( '/' ) ),
		) );
	}

	$product_ids = lfk_product_ids_matching_sku( $term, 6 );
	$remaining   = max( 0, 6 - count( $product_ids ) );

	if ( $remaining ) {
		$product_query = new WP_Query( array(
			's'                   => $term,
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $remaining,
			'fields'              => 'ids',
			'no_found_rows'       => true,
			'ignore_sticky_posts' => true,
			'lfk_include_sku'     => true,
		) );

		foreach ( $product_query->posts as $product_id ) {
			if ( ! in_array( $product_id, $product_ids, true ) ) {
				$product_ids[] = $product_id;
			}
		}
	}

	$content_ids = array();
	$remaining   = max( 0, 6 - count( $product_ids ) );

	if ( $remaining ) {
		$content_query = new WP_Query( array(
			's'                     => $term,
			'post_type'             => array( 'post', 'page' ),
			'post_status'           => 'publish',
			'posts_per_page'        => $remaining,
			'fields'                => 'ids',
			'no_found_rows'         => true,
			'ignore_sticky_posts'   => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );

		$content_ids = $content_query->posts;
	}

	$results = array();

	foreach ( $product_ids as $product_id ) {
		$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
		if ( ! $product ) {
			continue;
		}

		$summary = html_entity_decode( wp_strip_all_tags( $product->get_price_html() ), ENT_QUOTES, get_bloginfo( 'charset' ) );

		$results[] = array(
			'title'   => html_entity_decode( $product->get_name(), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'     => get_permalink( $product_id ),
			'image'   => get_the_post_thumbnail_url( $product_id, 'thumbnail' ) ?: '',
			'type'    => __( 'สินค้า', 'lfk-tailwind' ),
			'summary' => $summary,
		);
	}

	foreach ( $content_ids as $content_id ) {
		$post      = get_post( $content_id );
		$post_type = get_post_type( $post );
		$image     = get_the_post_thumbnail_url( $post, 'thumbnail' );
		$summary   = '';

		if ( has_excerpt( $post ) ) {
			$summary = wp_trim_words( get_the_excerpt( $post ), 12 );
		}
		$summary = html_entity_decode( $summary, ENT_QUOTES, get_bloginfo( 'charset' ) );

		$results[] = array(
			'title'   => html_entity_decode( get_the_title( $post ), ENT_QUOTES, get_bloginfo( 'charset' ) ),
			'url'     => get_permalink( $post ),
			'image'   => $image ?: '',
			'type'    => 'product' === $post_type ? __( 'สินค้า', 'lfk-tailwind' ) : ( 'post' === $post_type ? __( 'บทความ', 'lfk-tailwind' ) : __( 'หน้า', 'lfk-tailwind' ) ),
			'summary' => $summary,
		);
	}

	wp_send_json_success( array(
		'results'   => $results,
		'searchUrl' => add_query_arg( 's', $term, home_url( '/' ) ),
	) );
}

add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! lfk_is_product_archive_query( $query ) ) {
		return;
	}

	$tax_query  = (array) $query->get( 'tax_query' );
	$meta_query = (array) $query->get( 'meta_query' );

	foreach ( array( 'product_brand' => 'filter_brand', 'age' => 'filter_age' ) as $taxonomy => $key ) {
		$slugs = lfk_archive_filter_values( $key );
		if ( $slugs ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slugs,
				'operator' => 'IN',
			);
		}
	}

	$price = lfk_archive_selected_price_range();
	if ( $price['min'] || $price['max'] ) {
		$price_query = array(
			'key'  => '_price',
			'type' => 'DECIMAL',
		);

		if ( $price['min'] && $price['max'] ) {
			$price_query['value']   = array( $price['min'], $price['max'] );
			$price_query['compare'] = 'BETWEEN';
		} elseif ( $price['min'] ) {
			$price_query['value']   = $price['min'];
			$price_query['compare'] = '>=';
		} else {
			$price_query['value']   = $price['max'];
			$price_query['compare'] = '<=';
		}

		$meta_query[] = $price_query;
	}

	if ( $tax_query ) {
		$query->set( 'tax_query', $tax_query );
	}
	if ( $meta_query ) {
		$query->set( 'meta_query', $meta_query );
	}
} );

function lfk_is_local_site() {
	$host = wp_parse_url( home_url(), PHP_URL_HOST );
	return in_array( $host, array( '127.0.0.1', 'localhost' ), true ) || str_ends_with( (string) $host, '.local' );
}

function lfk_is_contact_page() {
	return is_page( 'contact-us' );
}

add_filter( 'gettext', function ( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain || ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return $translation;
	}

	if ( 'Cart totals' === $text ) {
		return __( 'ยอดรวมสินค้าในรถเข็น', 'lfk-tailwind' );
	}

	if ( 'Proceed to checkout' === $text ) {
		return __( 'ดำเนินการชำระเงิน', 'lfk-tailwind' );
	}

	return $translation;
}, 10, 3 );

add_filter( 'gettext', function ( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain || ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return $translation;
	}

	$checkout_translations = array(
		'Billing details' => __( 'รายละเอียดการเรียกเก็บเงิน', 'lfk-tailwind' ),
		'Ship to a different address?' => __( 'จัดส่งไปยังที่อยู่อื่นหรือไม่?', 'lfk-tailwind' ),
		'Your order' => __( 'การสั่งซื้อของคุณ', 'lfk-tailwind' ),
		'Have a coupon?' => __( 'มีคูปองมั้ย?', 'lfk-tailwind' ),
		'Click here to enter your code' => __( 'คลิกที่นี่เพื่อป้อนรหัสคูปองของคุณ', 'lfk-tailwind' ),
		'Returning customer?' => __( 'เป็นลูกค้าเก่าใช่ไหม?', 'lfk-tailwind' ),
		'Click here to login' => __( 'คลิกที่นี่เพื่อเข้าสู่ระบบ', 'lfk-tailwind' ),
		'Create an account?' => __( 'สร้างบัญชี?', 'lfk-tailwind' ),
	);

	return $checkout_translations[ $text ] ?? $translation;
}, 10, 3 );

add_filter( 'woocommerce_checkout_fields', function ( $fields ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return $fields;
	}

	if ( isset( $fields['billing']['billing_company'] ) ) {
		$fields['billing']['billing_company']['label'] = __( 'ชื่อบริษัท', 'lfk-tailwind' );
	}

	if ( isset( $fields['billing']['billing_email'] ) ) {
		$fields['billing']['billing_email']['label'] = __( 'ที่อยู่อีเมล์', 'lfk-tailwind' );
	}

	return $fields;
}, 1000 );

add_filter( 'woocommerce_form_field_args', function ( $args, $key ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return $args;
	}

	if ( 'billing_company' === $key ) {
		$args['label'] = __( 'ชื่อบริษัท', 'lfk-tailwind' );
	}

	if ( 'billing_email' === $key ) {
		$args['label'] = __( 'ที่อยู่อีเมล์', 'lfk-tailwind' );
	}

	return $args;
}, 1000, 2 );

add_filter( 'woocommerce_enable_order_notes_field', function ( $enabled ) {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	return $enabled;
} );

function lfk_should_trim_plugin_assets() {
	if ( is_admin() ) {
		return false;
	}

	if ( is_front_page() || is_home() || is_search() || is_singular( array( 'post', 'product' ) ) ) {
		return true;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return true;
	}

	if ( is_page( array_merge( array( 'promotion', 'ages', 'brands', 'contact-us', 'wishlists', 'my-account' ), lfk_static_page_slugs() ) ) ) {
		return true;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return true;
	}

	return is_post_type_archive( 'product' ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
}

function lfk_is_wishlist_page() {
	return is_page( 'wishlists' );
}

function lfk_is_woo_service_page() {
	return function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() || lfk_is_wishlist_page() );
}

function lfk_view_needs_cart_scripts() {
	if ( is_singular( 'product' ) || is_page( 'promotion' ) ) {
		return true;
	}

	if ( lfk_is_woo_service_page() ) {
		return true;
	}

	return false;
}

function lfk_dequeue_local_recaptcha() {
	if ( ! lfk_should_trim_plugin_assets() ) {
		return;
	}

	$styles = array(
		'addtoany',
		'berocket_lmp_style',
		'brands-styles',
		'classic-theme-styles',
		'e-animation-fadeIn',
		'e-apple-webkit',
		'e-popup',
		'e-swiper',
		'elementor-frontend',
		'elementor-gf-local-anuphan',
		'elementor-post-25298',
		'elementor-post-29086',
		'elementor-post-29093',
		'elementor-post-29126',
		'elementor-post-29322',
		'font-awesome',
		'htflexboxgrid',
		'photoswipe',
		'photoswipe-default-skin',
		'simple-line-icons-wl',
		'slick',
		'swiper',
		'wc-blocks-style',
		'wc-blocks-style-all-products',
		'widget-heading',
		'widget-icon-list',
		'widget-image',
		'widget-loop-carousel',
		'widget-loop-common',
		'widget-nav-menu',
		'widget-nested-carousel',
		'widget-post-info',
		'widget-search',
		'widget-social-icons',
		'widget-video',
		'widget-woocommerce-menu-cart',
		'widget-woocommerce-products',
		'woolentor-product-grid-editorial',
		'woolentor-product-grid-luxury',
		'woolentor-product-grid-magazine',
		'woolentor-product-grid-modern',
		'woolentor-quickview',
		'woolentor-widgets',
		'global-styles',
		'wp-block-library',
		'yith-wcan-shortcodes',
	);

	if ( ! lfk_is_contact_page() ) {
		$styles[] = 'contact-form-7';
	}

	if ( ! lfk_is_wishlist_page() ) {
		$styles[] = 'magnific-popup';
		$styles[] = 'wishlist-style';
	}

	if ( ! lfk_is_woo_service_page() ) {
		$styles[] = 'woocommerce-general';
		$styles[] = 'woocommerce-layout';
		$styles[] = 'woocommerce-smallscreen';
	}

	foreach ( $styles as $handle ) {
		wp_dequeue_style( $handle );
		wp_deregister_style( $handle );
	}

	$scripts = array(
		'addtoany-core',
		'addtoany-jquery',
		'berocket_lmp_js',
		'elementor-app-loader',
		'elementor-common',
		'elementor-frontend',
		'elementor-frontend-modules',
		'elementor-pro-app',
		'elementor-pro-frontend',
		'elementor-pro-webpack-runtime',
		'elementor-webpack-runtime',
		'imagesloaded',
		'photoswipe',
		'photoswipe-ui-default',
		'pro-elements-handlers',
		'slick',
		'smartmenus',
		'swiper',
		'wc-accounting',
		'wc-single-product',
		'woolentor-quickview',
		'yith-wcan-shortcodes',
	);

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		$scripts[] = 'googlepay-button-component';
		$scripts[] = 'omise-atome-js';
		$scripts[] = 'omise-download-promptpay-as-png';
		$scripts[] = 'selectWoo';
	}

	if ( ! lfk_is_contact_page() ) {
		$scripts[] = 'contact-form-7';
		$scripts[] = 'google-recaptcha';
		$scripts[] = 'swv';
		$scripts[] = 'wpcf7-recaptcha';
	}

	if ( ! lfk_is_wishlist_page() ) {
		$scripts[] = 'magnific-popup';
		$scripts[] = 'wishlist-script';
	}

	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		$scripts[] = 'sourcebuster-js';
		$scripts[] = 'wc-order-attribution';
	}

	foreach ( $scripts as $handle ) {
		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}

	if ( ! lfk_view_needs_cart_scripts() ) {
		foreach ( array( 'wc-add-to-cart', 'wc-jquery-blockui', 'wc-js-cookie', 'woocommerce', 'jquery', 'jquery-core', 'jquery-migrate' ) as $handle ) {
			wp_dequeue_script( $handle );
			wp_deregister_script( $handle );
		}
	}

	if ( ! lfk_is_contact_page() ) {
		wp_dequeue_script( 'google-recaptcha' );
		wp_dequeue_script( 'wpcf7-recaptcha' );
		wp_deregister_script( 'google-recaptcha' );
		wp_deregister_script( 'wpcf7-recaptcha' );
	}
}
add_action( 'wp_enqueue_scripts', 'lfk_dequeue_local_recaptcha', 1000 );
add_action( 'wp_print_styles', 'lfk_dequeue_local_recaptcha', 1 );
add_action( 'wp_print_footer_scripts', 'lfk_dequeue_local_recaptcha', 1 );

add_filter( 'addtoany_sharing_disabled', function ( $disabled ) {
	return $disabled || lfk_should_trim_plugin_assets();
} );

add_filter( 'addtoany_script_disabled', function ( $disabled ) {
	return $disabled || lfk_should_trim_plugin_assets();
} );

add_action( 'wp', function () {
	if ( ! lfk_should_trim_plugin_assets() || ! class_exists( '\Woolentor\Modules\QuickView\Frontend\Popup_Manager' ) ) {
		return;
	}

	$popup_manager = \Woolentor\Modules\QuickView\Frontend\Popup_Manager::instance();
	remove_action( 'woolentor_footer_render_content', array( $popup_manager, 'quick_view_html' ), 10 );
}, 20 );

function lfk_product_archive_result_count() {
	global $wp_query;

	$total = isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0;
	if ( $total < 1 ) {
		return '';
	}

	$per_page = (int) $wp_query->get( 'posts_per_page' );
	if ( $per_page < 1 ) {
		$per_page = $total;
	}

	$current = max( 1, (int) get_query_var( 'paged' ) );
	$first   = min( $total, ( $per_page * ( $current - 1 ) ) + 1 );
	$last    = min( $total, $per_page * $current );

	if ( 1 === $total ) {
		return __( 'Showing the single result', 'lfk-tailwind' );
	}

	if ( $total <= $per_page ) {
		return sprintf( __( 'Showing all %d results', 'lfk-tailwind' ), $total );
	}

	return sprintf( __( 'Showing %1$d-%2$d of %3$d results', 'lfk-tailwind' ), $first, $last, $total );
}

function lfk_product_archive_hero_image_id() {
	$term = get_queried_object();
	if ( ! $term instanceof WP_Term ) {
		return 0;
	}

	foreach ( array( 'single_page_image', 'thumbnail_id', 'small_image' ) as $meta_key ) {
		$image_id = (int) get_term_meta( $term->term_id, $meta_key, true );
		if ( $image_id ) {
			return $image_id;
		}
	}

	return 0;
}

function lfk_is_product_archive_query( $query ) {
	if ( $query->is_post_type_archive( 'product' ) || $query->is_tax( array( 'product_cat', 'product_tag', 'product_brand', 'age' ) ) ) {
		return true;
	}

	$taxonomy = (string) $query->get( 'taxonomy' );
	return $taxonomy && str_starts_with( $taxonomy, 'pa_' );
}

function lfk_archive_filter_values( $key ) {
	$value = isset( $_GET[ $key ] ) ? wp_unslash( $_GET[ $key ] ) : array();
	if ( ! is_array( $value ) ) {
		$value = explode( ',', (string) $value );
	}

	$values = array();
	foreach ( $value as $item ) {
		$slug = sanitize_title( $item );
		if ( $slug ) {
			$values[] = $slug;
		}
	}

	return array_values( array_unique( $values ) );
}

function lfk_archive_selected_price_range() {
	$min = isset( $_GET['min_price'] ) ? (float) wp_unslash( $_GET['min_price'] ) : 0;
	$max = isset( $_GET['max_price'] ) ? (float) wp_unslash( $_GET['max_price'] ) : 0;

	if ( ! $min && ! $max && isset( $_GET['lfk_price_range'] ) ) {
		$raw_range = wp_unslash( $_GET['lfk_price_range'] );
		if ( is_array( $raw_range ) ) {
			$raw_range = reset( $raw_range );
		}
		$range = preg_split( '/[:-]/', sanitize_text_field( $raw_range ) );
		$min   = isset( $range[0] ) ? (float) $range[0] : 0;
		$max   = isset( $range[1] ) ? (float) $range[1] : 0;
	}

	return array(
		'min' => max( 0, $min ),
		'max' => max( 0, $max ),
	);
}

function lfk_archive_context_tax_query( $exclude_filter_taxonomy = '' ) {
	$tax_query    = array();
	$queried_term = get_queried_object();

	if ( $queried_term instanceof WP_Term && taxonomy_exists( $queried_term->taxonomy ) ) {
		$tax_query[] = array(
			'taxonomy'         => $queried_term->taxonomy,
			'field'            => 'term_id',
			'terms'            => array( $queried_term->term_id ),
			'include_children' => true,
		);
	}

	foreach ( array( 'product_brand' => 'filter_brand', 'age' => 'filter_age' ) as $taxonomy => $key ) {
		if ( $taxonomy === $exclude_filter_taxonomy ) {
			continue;
		}

		$slugs = lfk_archive_filter_values( $key );
		if ( $slugs ) {
			$tax_query[] = array(
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $slugs,
				'operator' => 'IN',
			);
		}
	}

	if ( count( $tax_query ) > 1 ) {
		$tax_query['relation'] = 'AND';
	}

	return $tax_query;
}

function lfk_archive_context_meta_query() {
	$price      = lfk_archive_selected_price_range();
	$meta_query = array();

	if ( $price['min'] || $price['max'] ) {
		$price_query = array(
			'key'  => '_price',
			'type' => 'DECIMAL',
		);

		if ( $price['min'] && $price['max'] ) {
			$price_query['value']   = array( $price['min'], $price['max'] );
			$price_query['compare'] = 'BETWEEN';
		} elseif ( $price['min'] ) {
			$price_query['value']   = $price['min'];
			$price_query['compare'] = '>=';
		} else {
			$price_query['value']   = $price['max'];
			$price_query['compare'] = '<=';
		}

		$meta_query[] = $price_query;
	}

	return $meta_query;
}

function lfk_archive_context_product_ids( $exclude_filter_taxonomy = '' ) {
	static $cache = array();

	$cache_key = md5( wp_json_encode( array(
		'exclude' => $exclude_filter_taxonomy,
		'term'    => get_queried_object_id(),
		'query'   => array(
			'brand' => lfk_archive_filter_values( 'filter_brand' ),
			'age'   => lfk_archive_filter_values( 'filter_age' ),
			'price' => lfk_archive_selected_price_range(),
		),
	) ) );

	if ( isset( $cache[ $cache_key ] ) ) {
		return $cache[ $cache_key ];
	}

	$args = array(
		'post_type'              => 'product',
		'post_status'            => 'publish',
		'fields'                 => 'ids',
		'posts_per_page'         => -1,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	$tax_query = lfk_archive_context_tax_query( $exclude_filter_taxonomy );
	if ( $tax_query ) {
		$args['tax_query'] = $tax_query;
	}

	$meta_query = lfk_archive_context_meta_query();
	if ( $meta_query ) {
		$args['meta_query'] = $meta_query;
	}

	$cache[ $cache_key ] = get_posts( $args );

	return $cache[ $cache_key ];
}

function lfk_archive_filter_terms_for_context( $taxonomy ) {
	$product_ids = lfk_archive_context_product_ids( $taxonomy );
	if ( ! $product_ids ) {
		return array();
	}

	$object_terms = wp_get_object_terms( $product_ids, $taxonomy, array(
		'fields' => 'all_with_object_id',
	) );

	if ( ! $object_terms || is_wp_error( $object_terms ) ) {
		return array();
	}

	$terms       = array();
	$term_counts = array();
	foreach ( $object_terms as $term ) {
		if ( ! $term instanceof WP_Term || empty( $term->slug ) || empty( $term->object_id ) ) {
			continue;
		}

		$terms[ $term->slug ]                             = $term;
		$term_counts[ $term->slug ][ (int) $term->object_id ] = true;
	}

	foreach ( $terms as $slug => $term ) {
		$terms[ $slug ]->count = isset( $term_counts[ $slug ] ) ? count( $term_counts[ $slug ] ) : 0;
	}

	return lfk_archive_sort_filter_terms( array_values( $terms ), $taxonomy );
}

function lfk_archive_age_sort_value( WP_Term $term ) {
	$order = array(
		'18-months'  => 10,
		'2-years'    => 20,
		'3-4-years'  => 30,
		'5-7-years'  => 40,
		'8-up'       => 50,
	);

	if ( isset( $order[ $term->slug ] ) ) {
		return $order[ $term->slug ];
	}

	if ( preg_match( '/(\d+)/', $term->name, $match ) ) {
		return (int) $match[1] * 10;
	}

	return 999;
}

function lfk_archive_sort_filter_terms( $terms, $taxonomy ) {
	usort( $terms, function ( $a, $b ) use ( $taxonomy ) {
		if ( 'age' === $taxonomy ) {
			$age_compare = lfk_archive_age_sort_value( $a ) <=> lfk_archive_age_sort_value( $b );
			if ( 0 !== $age_compare ) {
				return $age_compare;
			}
		}

		return strnatcasecmp( $a->name, $b->name );
	} );

	return $terms;
}

function lfk_archive_filter_key( $taxonomy ) {
	if ( 'product_brand' === $taxonomy ) {
		return 'filter_brand';
	}
	if ( 'age' === $taxonomy ) {
		return 'filter_age';
	}

	return 'filter_' . sanitize_key( $taxonomy );
}

function lfk_archive_filter_action_url() {
	return remove_query_arg(
		array( 'paged', 'product-page', 'filter_brand', 'filter_age', 'min_price', 'max_price', 'lfk_price_range' ),
		get_pagenum_link( 1 )
	);
}

function lfk_archive_filter_hidden_inputs() {
	foreach ( array( 'orderby', 's', 'post_type' ) as $key ) {
		if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) {
			printf(
				'<input type="hidden" name="%1$s" value="%2$s">',
				esc_attr( $key ),
				esc_attr( sanitize_text_field( wp_unslash( $_GET[ $key ] ) ) )
			);
		}
	}
}

function lfk_archive_filter_terms( $taxonomy, $heading, $limit = 12 ) {
	$terms = array_slice( lfk_archive_filter_terms_for_context( $taxonomy ), 0, $limit );
	if ( ! $terms ) {
		return;
	}

	$key      = lfk_archive_filter_key( $taxonomy );
	$selected = lfk_archive_filter_values( $key );
	?>
	<div class="lfk-filter-group">
		<div class="lfk-filter-heading"><?php echo esc_html( $heading ); ?></div>
		<ul>
			<?php foreach ( $terms as $term ) : ?>
				<?php $is_selected = in_array( $term->slug, $selected, true ); ?>
				<li>
					<label class="lfk-filter-option<?php echo $is_selected ? ' is-active' : ''; ?>">
						<input type="checkbox" name="<?php echo esc_attr( $key ); ?>[]" value="<?php echo esc_attr( $term->slug ); ?>" <?php checked( $is_selected ); ?>>
						<span class="lfk-filter-check" aria-hidden="true"></span>
						<span class="lfk-filter-label"><?php echo esc_html( $term->name ); ?></span>
					</label>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

function lfk_archive_price_filters() {
	$ranges = array(
		array( 'min' => 99, 'max' => 2349, 'label' => '99฿ to 2349฿' ),
		array( 'min' => 2350, 'max' => 5949, 'label' => '2350฿ to 5949฿' ),
		array( 'min' => 5950, 'max' => 10999, 'label' => '5950฿ to 10999฿' ),
		array( 'min' => 11000, 'max' => 17999, 'label' => '11000฿ to 17999฿' ),
	);
	$selected = lfk_archive_selected_price_range();
	?>
	<div class="lfk-filter-group">
		<div class="lfk-filter-heading"><?php esc_html_e( 'กรองตามราคา', 'lfk-tailwind' ); ?></div>
		<select class="lfk-price-select" name="lfk_price_range" aria-label="<?php esc_attr_e( 'กรองตามราคา', 'lfk-tailwind' ); ?>">
			<option value=""><?php esc_html_e( 'เลือกช่วงราคา', 'lfk-tailwind' ); ?></option>
			<?php foreach ( $ranges as $range ) : ?>
				<?php $is_selected = (float) $range['min'] === $selected['min'] && (float) $range['max'] === $selected['max']; ?>
				<option value="<?php echo esc_attr( $range['min'] . '-' . $range['max'] ); ?>" <?php selected( $is_selected ); ?>><?php echo esc_html( $range['label'] ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}

function lfk_svg_icon( $name ) {
	$icons = array(
		'menu' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>',
		'search' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4.5-4.5"/></svg>',
		'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21l7.8-7.6 1-1a5.5 5.5 0 0 0 0-7.8Z"/></svg>',
		'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.6-4 4.2-6 8-6s6.4 2 8 6"/></svg>',
		'globe' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2c3 3 4 6 4 10s-1 7-4 10M12 2C9 5 8 8 8 12s1 7 4 10"/></svg>',
		'cart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 6h15l-2 8H8L6 3H3"/><circle cx="9" cy="20" r="1.7"/><circle cx="18" cy="20" r="1.7"/></svg>',
		'chevron-left' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>',
		'chevron-right' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>',
		'chevron-up' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m18 15-6-6-6 6"/></svg>',
		'play' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.5v13l10-6.5-10-6.5Z"/></svg>',
		'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v15H3V6a2 2 0 0 1 2-2Z"/></svg>',
		'comment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/></svg>',
		'facebook' => '<svg viewBox="0 0 512 512" fill="currentColor" aria-hidden="true"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 124 91 226 209 245V328h-63v-72h63v-55c0-62 37-96 94-96 27 0 56 5 56 5v61h-31c-31 0-41 19-41 39v46h69l-11 72h-58v173c118-19 209-121 209-245Z"/></svg>',
		'instagram' => '<svg viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M224 141c-64 0-115 51-115 115s51 115 115 115 115-51 115-115-51-115-115-115Zm0 190c-41 0-75-34-75-75s34-75 75-75 75 34 75 75-34 75-75 75Zm147-195c0 15-12 27-27 27s-27-12-27-27 12-27 27-27 27 12 27 27Zm76 28c-2-36-10-68-36-94s-58-34-94-36c-37-2-148-2-185 0-36 2-68 10-94 36S4 128 2 164c-2 37-2 148 0 185 2 36 10 68 36 94s58 34 94 36c37 2 148 2 185 0 36-2 68-10 94-36s34-58 36-94c2-37 2-148 0-185ZM399 388c-8 20-23 35-43 43-30 12-100 9-132 9s-102 3-132-9c-20-8-35-23-43-43-12-30-9-100-9-132s-3-102 9-132c8-20 23-35 43-43 30-12 100-9 132-9s102-3 132 9c20 8 35 23 43 43 12 30 9 100 9 132s3 102-9 132Z"/></svg>',
		'tiktok' => '<svg viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M448 210a210 210 0 0 1-123-39v178a163 163 0 1 1-140-161v90a75 75 0 1 0 52 71V0h88c0 8 1 15 2 22 7 36 28 67 58 88 18 13 40 20 63 20v80Z"/></svg>',
		'line' => '<svg viewBox="0 0 448 512" fill="currentColor" aria-hidden="true"><path d="M272 204v71c0 2-1 3-3 3h-12c-1 0-2-1-3-1l-32-44v42c0 2-2 3-4 3h-11c-2 0-3-1-3-3v-71c0-2 1-3 3-3h11c1 0 2 1 3 1l33 44v-42c0-2 1-3 3-3h12c2 0 3 1 3 3Zm-82-3h-11c-2 0-3 1-3 3v71c0 2 1 3 3 3h11c2 0 3-1 3-3v-71c0-2-1-3-3-3Zm-27 60h-31v-57c0-2-1-3-3-3h-11c-2 0-3 1-3 3v71c0 2 1 3 3 3h46c2 0 3-1 3-3v-11c0-2-2-3-4-3Zm169-60h-46c-2 0-3 1-3 3v71c0 2 1 3 3 3h46c2 0 3-1 3-3v-11c0-2-1-3-3-3h-31v-12h31c2 0 3-2 3-4v-11c0-2-1-3-3-3h-31v-12h31c2 0 3-2 3-4v-11c0-2-1-3-3-3ZM448 114v285c0 45-37 81-82 81H81c-45 0-81-37-81-82V113c0-45 37-81 82-81h285c45 0 81 37 81 82ZM386 236c0-73-73-132-163-132S60 163 60 236c0 65 58 120 136 131 19 4 17 11 13 37-1 4-3 16 14 9s94-55 128-95c24-26 35-52 35-82Z"/></svg>',
		'youtube' => '<svg viewBox="0 0 576 512" fill="currentColor" aria-hidden="true"><path d="M550 124c-6-24-25-42-49-49C459 64 288 64 288 64S117 64 75 75c-24 7-43 25-49 49-11 43-11 132-11 132s0 89 11 132c6 24 25 42 49 49 42 11 213 11 213 11s171 0 213-11c24-7 43-25 49-49 11-43 11-132 11-132s0-89-11-132ZM232 338V175l143 81-143 82Z"/></svg>',
	);

	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

function lfk_remote_upload_url( $path ) {
	$relative = ltrim( $path, '/' );
	$uploads  = wp_get_upload_dir();
	if ( ! empty( $uploads['baseurl'] ) && ! empty( $uploads['basedir'] ) ) {
		$local_file = trailingslashit( $uploads['basedir'] ) . $relative;
		if ( file_exists( $local_file ) ) {
			return lfk_prefer_local_upload_webp_url( trailingslashit( $uploads['baseurl'] ) . $relative );
		}
	}

	return 'https://www.learningforkidz.com/wp-content/uploads/' . $relative;
}

function lfk_prefer_local_upload_webp_url( $url ) {
	$uploads = wp_get_upload_dir();
	if ( empty( $uploads['baseurl'] ) || empty( $uploads['basedir'] ) || ! str_starts_with( $url, $uploads['baseurl'] ) ) {
		return $url;
	}

	$relative = ltrim( substr( $url, strlen( $uploads['baseurl'] ) ), '/' );
	$file     = trailingslashit( $uploads['basedir'] ) . $relative;
	if ( file_exists( $file . '.webp' ) ) {
		$url .= '.webp';
	}

	return lfk_version_local_upload_url( $url );
}

function lfk_logo_url() {
	$custom_logo_id = get_theme_mod( 'custom_logo' );
	if ( $custom_logo_id ) {
		return wp_get_attachment_image_url( $custom_logo_id, 'full' );
	}

	return content_url( 'uploads/2025/06/lK-logo.png' );
}

function lfk_cart_count() {
	return function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}

function lfk_cart_total() {
	return function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_total() : '0.00฿';
}

function lfk_section_heading( $text ) {
	printf( '<h2 class="lfk-section-heading">%s</h2>', esc_html( $text ) );
}

function lfk_article_card( $post = null, $index = 0 ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return;
	}

	$image_loading = $index < 8 ? 'eager' : 'lazy';
	$thumbnail_id  = get_post_thumbnail_id( $post );
	$image_style   = '';

	if ( $thumbnail_id ) {
		$image_data = wp_get_attachment_image_src( $thumbnail_id, 'medium' );
		if ( $image_data && ! empty( $image_data[1] ) && ! empty( $image_data[2] ) ) {
			$image_ratio = (float) $image_data[2] / (float) $image_data[1];
			$image_style = sprintf(
				'--lfk-article-mobile-height:%spx;--lfk-article-desktop-height:%spx;',
				number_format( 390 * $image_ratio, 3, '.', '' ),
				number_format( 456 * $image_ratio, 3, '.', '' )
			);
		}
	}

	$classes = 'lfk-article-card';
	if ( 'product' === get_post_type( $post ) ) {
		$classes .= ' product';
	}
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<a class="lfk-article-image" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<?php
				$image_attrs = array(
					'loading' => $image_loading,
					'style'   => $image_style,
				);
				if ( $index < 2 ) {
					$image_attrs['class']         = 'skip-lazy';
					$image_attrs['data-no-lazy']  = '1';
					$image_attrs['fetchpriority'] = 'high';
				}
				echo lfk_local_attachment_image( $thumbnail_id, 'medium', $image_attrs );
				?>
			<?php else : ?>
				<img class="lfk-article-fallback" src="<?php echo esc_url( lfk_logo_url() ); ?>" alt="" loading="<?php echo esc_attr( $image_loading ); ?>">
			<?php endif; ?>
		</a>
		<div class="lfk-article-author">
			<img class="lfk-article-avatar-image skip-lazy" src="<?php echo esc_url( lfk_logo_url() ); ?>" alt="<?php esc_attr_e( 'Learning for Kidz', 'lfk-tailwind' ); ?>" width="60" height="60" loading="<?php echo esc_attr( $image_loading ); ?>" decoding="async" data-no-lazy="1">
		</div>
		<div class="lfk-article-body">
			<h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<div class="lfk-article-meta">
				<span><?php echo lfk_svg_icon( 'calendar' ); ?><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
				<span><?php echo lfk_svg_icon( 'comment' ); ?><?php echo esc_html( get_comments_number_text( 'ไม่มีความเห็น', '1 ความเห็น', '% ความเห็น', $post ) ); ?></span>
			</div>
		</div>
	</article>
	<?php
}

function lfk_content_with_image_aspect_ratios( $content ) {
	return preg_replace_callback(
		'/<img\\b([^>]*)>/i',
		function ( $matches ) {
			$attributes = $matches[1];
			if (
				! preg_match( "/\\bwidth=[\"'](\\d+)[\"']/i", $attributes, $width_match )
				|| ! preg_match( "/\\bheight=[\"'](\\d+)[\"']/i", $attributes, $height_match )
			) {
				return $matches[0];
			}

			$ratio_style = sprintf( 'aspect-ratio:%d / %d;', (int) $width_match[1], (int) $height_match[1] );
			if ( preg_match( "/\\bstyle=[\"']([^\"']*)[\"']/i", $attributes, $style_match ) ) {
				$attributes = preg_replace(
					"/\\bstyle=[\"'][^\"']*[\"']/i",
					'style="' . esc_attr( rtrim( $style_match[1], ';' ) . ';' . $ratio_style ) . '"',
					$attributes,
					1
				);
			} else {
				$attributes .= ' style="' . esc_attr( $ratio_style ) . '"';
			}

			return '<img' . $attributes . '>';
		},
		$content
	);
}

function lfk_product_card_image( $product, $attributes = array() ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$attributes = wp_parse_args(
		$attributes,
		array(
			'decoding' => 'async',
			'loading'  => 'lazy',
		)
	);

	$image_id = $product->get_image_id();
	if ( ! $image_id ) {
		return $product->get_image( 'woocommerce_thumbnail', $attributes );
	}

	$image = wp_get_attachment_image_src( $image_id, 'woocommerce_thumbnail' );
	if ( ! $image ) {
		return $product->get_image( 'woocommerce_thumbnail', $attributes );
	}

	$alt = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
	if ( '' === $alt ) {
		$alt = $product->get_name();
	}

	$default_class = 'attachment-woocommerce_thumbnail size-woocommerce_thumbnail wp-post-image';
	$attributes    = wp_parse_args(
		$attributes,
		array(
			'alt'    => $alt,
			'class'  => $default_class,
			'height' => (int) $image[2],
			'src'    => lfk_prefer_local_upload_webp_url( $image[0] ),
			'width'  => (int) $image[1],
		)
	);
	if ( ! empty( $attributes['class'] ) && ! str_contains( $attributes['class'], $default_class ) ) {
		$attributes['class'] = trim( $default_class . ' ' . $attributes['class'] );
	}

	$html = '<img';
	foreach ( $attributes as $name => $value ) {
		if ( false === $value || null === $value || '' === $value ) {
			continue;
		}
		$html .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}
	return $html . '>';
}

function lfk_local_attachment_image( $attachment_id, $size, $attributes = array() ) {
	$image = wp_get_attachment_image_src( $attachment_id, $size );
	if ( ! $image ) {
		return wp_get_attachment_image( $attachment_id, $size, false, $attributes );
	}

	$alt = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	if ( '' === $alt ) {
		$alt = get_the_title( $attachment_id );
	}

	$default_class = 'attachment-' . esc_attr( $size ) . ' size-' . esc_attr( $size );
	$attributes    = wp_parse_args(
		$attributes,
		array(
			'alt'      => $alt,
			'class'    => $default_class,
			'decoding' => 'async',
			'loading'  => 'lazy',
		)
	);
	if ( ! empty( $attributes['class'] ) && ! str_contains( $attributes['class'], $default_class ) ) {
		$attributes['class'] = trim( $default_class . ' ' . $attributes['class'] );
	}

	$attributes['src']    = lfk_prefer_local_upload_webp_url( $image[0] );
	$attributes['width']  = (int) $image[1];
	$attributes['height'] = (int) $image[2];

	$html = '<img';
	foreach ( $attributes as $name => $value ) {
		if ( false === $value || null === $value || '' === $value ) {
			continue;
		}
		$html .= sprintf( ' %s="%s"', esc_attr( $name ), esc_attr( $value ) );
	}
	return $html . '>';
}

function lfk_product_card( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$classes    = implode( ' ', array_map( 'sanitize_html_class', wc_get_product_class( 'lfk-product-card', $product ) ) );
	?>
	<article class="<?php echo esc_attr( $classes ); ?>">
		<a class="lfk-product-image" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
			<?php echo lfk_product_card_image( $product ); ?>
		</a>
		<h2 class="lfk-product-title"><a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
		<div class="lfk-product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<a class="lfk-product-wishlist" href="<?php echo esc_url( home_url( '/wishlists/' ) ); ?>">
			<?php echo lfk_svg_icon( 'heart' ); ?>
			<span>Add to Wishlist</span>
		</a>
		<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
			<a
				href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
				data-quantity="1"
				data-product_id="<?php echo esc_attr( $product_id ); ?>"
				data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
				class="lfk-add-to-cart button <?php echo esc_attr( $product->is_type( 'simple' ) ? 'product_type_simple add_to_cart_button ajax_add_to_cart' : 'product_type_' . $product->get_type() ); ?>"
				aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
				rel="nofollow"
			><?php echo esc_html( $product->add_to_cart_text() ); ?></a>
		<?php endif; ?>
	</article>
	<?php
}

function lfk_archive_product_title( $product ) {
	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$overrides = array(
		32960 => 'CANDY RETAIL & Wobble Tower เล่นสนุกทั้งครอบครัว✨',
		32962 => 'Feed the Woodpecker & Sensory Bottles✨',
		32988 => 'Numberblocks 1-10 & Playing Cards✨',
		32978 => 'Numberblocks Puzzle1 & Numberblocks Counters✨',
		32995 => 'Numberblocks® One to Five Sensory Bottles & SPIKE THE FINE MOTOR HEDGEHOG✨',
		29859 => '[3ขวบ+] ขวดกระตุ้นประสาทสัมผัสนัมเบอร์บล็อกส์ ตัวเลข 1-5 “Numberblocks® One to Five Sensory Bottles [Hand2Mind]',
		27922 => '[อายุ 3+] นัมเบอร์บล็อก กิจกรรม 21-30 (Numberblocks : 21 – 30 Activity Set) [Hand2Mind]',
	);

	$title = $overrides[ $product->get_id() ] ?? $product->get_name();
	return str_replace( 'Numberblobs', 'Numberblocks', $title );
}

function lfk_archive_product_card( $product, $image_attributes = array() ) {
	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$product_id = $product->get_id();
	$classes    = implode( ' ', array_map( 'sanitize_html_class', wc_get_product_class( 'lfk-archive-product-card', $product ) ) );
	?>
	<li class="<?php echo esc_attr( $classes ); ?>">
		<a class="lfk-product-image" href="<?php echo esc_url( get_permalink( $product_id ) ); ?>">
			<?php echo lfk_product_card_image( $product, $image_attributes ); ?>
		</a>
		<h2 class="lfk-product-title"><a href="<?php echo esc_url( get_permalink( $product_id ) ); ?>"><?php echo esc_html( lfk_archive_product_title( $product ) ); ?></a></h2>
		<div class="lfk-product-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
		<a class="lfk-product-wishlist" href="<?php echo esc_url( home_url( '/wishlists/' ) ); ?>">
			<?php echo lfk_svg_icon( 'heart' ); ?>
			<span>Add to Wishlist</span>
		</a>
		<a
			href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
			data-quantity="1"
			data-product_id="<?php echo esc_attr( $product_id ); ?>"
			data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
			class="lfk-add-to-cart button <?php echo esc_attr( $product->is_type( 'simple' ) ? 'product_type_simple add_to_cart_button ajax_add_to_cart' : 'product_type_' . $product->get_type() ); ?>"
			aria-label="<?php echo esc_attr( $product->add_to_cart_description() ); ?>"
			rel="nofollow"
		><?php esc_html_e( 'หยิบใส่ตะกร้า', 'lfk-tailwind' ); ?></a>
	</li>
	<?php
}

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$cart_count                    = lfk_cart_count();
	$fragments['.lfk-cart-count'] = '<span class="lfk-cart-count' . ( $cart_count > 0 ? '' : ' is-empty' ) . '">' . esc_html( $cart_count ) . '</span>';
	$fragments['.lfk-cart-total'] = '<span class="lfk-cart-total">' . wp_kses_post( lfk_cart_total() ) . '</span>';
	return $fragments;
} );

add_filter( 'posts_search', 'lfk_product_search_by_sku', 9999, 2 );
function lfk_product_search_by_sku( $search, $wp_query ) {
	global $wpdb;

	if ( is_admin() || ! isset( $wp_query->query_vars['s'] ) ) {
		return $search;
	}

	if ( ! $wp_query->is_search() && ! $wp_query->get( 'lfk_include_sku' ) ) {
		return $search;
	}

	if ( ! lfk_search_query_includes_products( $wp_query ) ) {
		return $search;
	}

	$product_ids = lfk_product_ids_matching_sku( $wp_query->query_vars['s'], 20 );
	if ( ! $product_ids ) {
		return $search;
	}

	$sku_search = "{$wpdb->posts}.ID IN (" . implode( ',', array_map( 'absint', $product_ids ) ) . ')';

	if ( '' === trim( $search ) ) {
		return " AND {$sku_search}";
	}

	return str_replace( 'AND (((', "AND (({$sku_search}) OR ((", $search );
}

add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	unset( $items['downloads'] );
	return $items;
} );

add_filter( 'woocommerce_gateway_title', function ( $title, $id ) {
	return 'bacs' === $id ? 'โอนผ่านธนาคาร' : $title;
}, 10, 2 );

add_filter( 'woocommerce_email_recipient_new_order', function ( $recipient, $order ) {
	if ( $order && $order->get_status() !== 'processing' && $order->get_status() !== 'completed' ) {
		return '';
	}
	return $recipient;
}, 10, 2 );

add_action( 'woocommerce_thankyou_order_received_text', function () {
	echo '<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><span class="span_1">ขอบคุณมากค่ะที่เลือกซื้อของเล่นเสริมพัฒนาการกับทางร้าน! 💕</span><span class="span_2">เรารู้สึกขอบคุณอย่างมากที่คุณเลือกของเล่นเสริมพัฒนาการสำหรับน้องจากเรา เราหวังว่าของเล่นเหล่านี้จะช่วยพัฒนาทักษะและความคิดสร้างสรรค์ของน้องได้ดีค่ะ สำหรับการจัดส่ง: เมื่อของเล่นออกจากร้านแล้ว เราจะส่งเลขติดตามพัสดุให้คุณภายในวันนี้ ถ้าหากกดสั่งซื้อเข้ามาหลัง 14:00 ทางเราจะดำเนินการจัดส่งให้คุณในวันถัดไปค่ะ เพื่อให้คุณสามารถติดตามสถานะการจัดส่งได้</span><span class="span_3">หากมีข้อสงสัยเกี่ยวกับการใช้งานของเล่น สามารถติดต่อเราได้ตลอดเวลาค่ะ</span><span class="span_4">ขอบคุณอีกครั้งที่ให้ความไว้วางใจ และหวังว่าน้องจะชอบของเล่นใหม่นะคะ 🙏</span></p>';
}, 10, 1 );

add_filter( 'woocommerce_get_stock_html', 'lfk_custom_stock_html', 10, 2 );
function lfk_custom_stock_html( $html, $product ) {
	if ( ! is_product() || ! $product->managing_stock() ) {
		return $html;
	}

	$qty = (int) $product->get_stock_quantity();
	if ( $qty <= 0 ) {
		return '<p class="stock out-of-stock" style="color:#d63638;font-weight:600;">สินค้าหมด</p>';
	}

	if ( $qty > 100 ) {
		$text  = 'มีมากกว่า 100 ชิ้น';
		$color = '#00a32a';
	} elseif ( $qty <= 5 ) {
		$text  = sprintf( 'เหลือ %d ชิ้น', $qty );
		$color = '#dba617';
	} else {
		$text  = sprintf( 'เหลือ %d ชิ้น', $qty );
		$color = '#00a32a';
	}

	$out = sprintf( '<p class="stock in-stock" style="color:%s;font-weight:600;">%s</p>', esc_attr( $color ), esc_html( $text ) );

	$last_sync = get_option( 'thaiadapp_odoo_last_sync', array() );
	$sync_time = isset( $last_sync['time'] ) ? $last_sync['time'] : 0;
	if ( $sync_time ) {
		$minutes = (int) floor( ( time() - (int) $sync_time ) / 60 );
		if ( $minutes < 1 ) {
			$ago = 'เมื่อสักครู่';
		} elseif ( $minutes < 60 ) {
			$ago = sprintf( '%d นาทีที่แล้ว', $minutes );
		} else {
			$ago = sprintf( '%d ชั่วโมงที่แล้ว', (int) floor( $minutes / 60 ) );
		}
		$out .= sprintf( '<p class="stock-sync-time" style="font-size:0.85em;color:#999;margin-top:-8px;">อัปเดต %s</p>', esc_html( $ago ) );
	}

	$incoming_qty  = get_post_meta( $product->get_id(), '_odoo_incoming_qty', true );
	$incoming_date = get_post_meta( $product->get_id(), '_odoo_incoming_date', true );
	if ( ! empty( $incoming_qty ) && (float) $incoming_qty > 0 ) {
		$incoming_text = sprintf( 'สินค้ากำลังมา: %s ชิ้น', number_format( (float) $incoming_qty, 0 ) );
		if ( $incoming_date ) {
			$day   = (int) date( 'j', strtotime( $incoming_date ) );
			$month = date( 'F', strtotime( $incoming_date ) );
			if ( $day <= 10 ) {
				$period = 'ต้นเดือน' . $month;
			} elseif ( $day <= 20 ) {
				$period = 'กลางเดือน' . $month;
			} else {
				$period = 'ปลายเดือน' . $month;
			}
			$incoming_text .= sprintf( ' (คาดว่าจะขายได้%s)', $period );
		}
		$out .= sprintf( '<p class="stock-incoming" style="font-size:0.9em;color:#2271b1;margin-top:-4px;">%s</p>', esc_html( $incoming_text ) );
	}

	return $out;
}
