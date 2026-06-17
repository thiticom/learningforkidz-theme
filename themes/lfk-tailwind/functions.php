<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LFK_TAILWIND_VERSION', '0.1.0' );

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
} );

add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'lfk-theme' !== $handle ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}, 10, 2 );

function lfk_theme_template_path( $template_name ) {
	$template = locate_template( $template_name );
	return $template ? $template : '';
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
		return false;
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

	if ( is_page( array( 'promotion', 'ages', 'brands', 'contact-us', 'wishlists', 'my-account' ) ) ) {
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
	if ( is_front_page() || is_singular( 'product' ) || is_search() || is_page( 'promotion' ) ) {
		return true;
	}

	if ( lfk_is_woo_service_page() ) {
		return true;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return true;
	}

	return is_post_type_archive( 'product' ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() );
}

function lfk_dequeue_local_recaptcha() {
	if ( ! lfk_should_trim_plugin_assets() ) {
		return;
	}

	$styles = array(
		'addtoany',
		'berocket_lmp_style',
		'brands-styles',
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
		'elementor-frontend',
		'elementor-frontend-modules',
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
		foreach ( array( 'wc-add-to-cart', 'wc-jquery-blockui', 'wc-js-cookie', 'woocommerce' ) as $handle ) {
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

function lfk_archive_filter_terms( $taxonomy, $heading, $limit = 12 ) {
	$terms = get_terms( array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'number'     => $limit,
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return;
	}
	?>
	<div class="lfk-filter-group">
		<h3><?php echo esc_html( $heading ); ?></h3>
		<ul>
			<?php foreach ( $terms as $term ) : ?>
				<li>
					<a class="<?php echo is_tax( $taxonomy, $term->slug ) ? 'is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
						<span><?php echo esc_html( $term->name ); ?></span>
						<small><?php echo esc_html( (string) $term->count ); ?></small>
					</a>
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
	?>
	<div class="lfk-filter-group">
		<h3><?php esc_html_e( 'กรองตามราคา', 'lfk-tailwind' ); ?></h3>
		<ul>
			<?php foreach ( $ranges as $range ) : ?>
				<?php $url = add_query_arg( array( 'min_price' => $range['min'], 'max_price' => $range['max'] ), get_pagenum_link( 1 ) ); ?>
				<li><a href="<?php echo esc_url( $url ); ?>"><span><?php echo esc_html( $range['label'] ); ?></span></a></li>
			<?php endforeach; ?>
		</ul>
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
	return 'https://www.learningforkidz.com/wp-content/uploads/' . ltrim( $path, '/' );
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
	?>
	<article class="lfk-article-card">
		<a class="lfk-article-image" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
			<?php if ( has_post_thumbnail( $post ) ) : ?>
				<?php echo get_the_post_thumbnail( $post, 'medium_large', array( 'loading' => $image_loading ) ); ?>
			<?php else : ?>
				<img class="lfk-article-fallback" src="<?php echo esc_url( lfk_logo_url() ); ?>" alt="" loading="<?php echo esc_attr( $image_loading ); ?>">
			<?php endif; ?>
		</a>
		<div class="lfk-article-body">
			<h3><a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo esc_html( get_the_title( $post ) ); ?></a></h3>
			<div class="lfk-article-meta">
				<span><?php echo lfk_svg_icon( 'calendar' ); ?><?php echo esc_html( get_the_date( '', $post ) ); ?></span>
				<span><?php echo lfk_svg_icon( 'comment' ); ?><?php echo esc_html( get_comments_number_text( 'No Comments', '1 Comment', '% Comments', $post ) ); ?></span>
			</div>
		</div>
	</article>
	<?php
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
			<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); ?>
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

add_filter( 'woocommerce_add_to_cart_fragments', function ( $fragments ) {
	$fragments['.lfk-cart-count'] = '<span class="lfk-cart-count">' . esc_html( lfk_cart_count() ) . '</span>';
	$fragments['.lfk-cart-total'] = '<span class="lfk-cart-total">' . wp_kses_post( lfk_cart_total() ) . '</span>';
	return $fragments;
} );

add_filter( 'posts_search', 'lfk_product_search_by_sku', 9999, 2 );
function lfk_product_search_by_sku( $search, $wp_query ) {
	global $wpdb;

	if ( is_admin() || ! is_search() || ! isset( $wp_query->query_vars['s'] ) ) {
		return $search;
	}

	$post_type = $wp_query->query_vars['post_type'];
	if ( ( ! is_array( $post_type ) && 'product' !== $post_type ) || ( is_array( $post_type ) && ! in_array( 'product', $post_type, true ) ) ) {
		return $search;
	}

	$product_id = wc_get_product_id_by_sku( $wp_query->query_vars['s'] );
	if ( ! $product_id ) {
		return $search;
	}

	$product = wc_get_product( $product_id );
	if ( $product && $product->is_type( 'variation' ) ) {
		$product_id = $product->get_parent_id();
	}

	return str_replace( 'AND (((', "AND (({$wpdb->posts}.ID IN (" . (int) $product_id . ')) OR ((', $search );
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
