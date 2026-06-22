<?php
/**
 * Minimal staging page cache for anonymous PageSpeed runs.
 *
 * This drop-in is intentionally narrow: it only caches GET/HEAD requests for
 * the bare homepage, article listing, directory landing pages, product
 * category archives, and product brand archives
 * with no query string, and it bypasses common WordPress, WooCommerce,
 * wishlist, and translation cookies.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'lfk_staging_page_cache_path_key' ) ) {
	function lfk_staging_page_cache_path_key() {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
		if ( 'GET' !== $method && 'HEAD' !== $method ) {
			return false;
		}

		if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
			return false;
		}

		$host = isset( $_SERVER['HTTP_HOST'] ) ? strtolower( (string) $_SERVER['HTTP_HOST'] ) : '';
		if ( 'staging.learningforkidz.com' !== preg_replace( '/:\d+$/', '', $host ) ) {
			return false;
		}

		foreach ( $_COOKIE as $name => $value ) {
			if ( preg_match( '/^(wordpress_logged_in_|wp-postpass_|comment_author_|woocommerce_|wp_woocommerce_session_|yith_wcwl_session_|googtrans|gt_)/', (string) $name ) ) {
				return false;
			}
		}

		$path = isset( $_SERVER['REQUEST_URI'] ) ? parse_url( (string) $_SERVER['REQUEST_URI'], PHP_URL_PATH ) : '/';
		if ( '/' === $path ) {
			return 'home';
		}

		if ( '/ages/' === $path ) {
			return 'ages';
		}

		if ( '/brands/' === $path ) {
			return 'brands';
		}

		if ( '/article/' === $path ) {
			return 'article';
		}

		if ( preg_match( '#^/product-category/[a-z0-9/_-]+/$#', (string) $path ) ) {
			return 'product-category-' . substr( hash( 'sha256', (string) $path ), 0, 16 );
		}

		if ( preg_match( '#^/product-brand/[a-z0-9/_-]+/$#', (string) $path ) ) {
			return 'product-brand-' . substr( hash( 'sha256', (string) $path ), 0, 16 );
		}

		return false;
	}
}

if ( ! function_exists( 'lfk_staging_page_cache_file' ) ) {
	function lfk_staging_page_cache_file( $cache_key ) {
		$base_dir = defined( 'WP_CONTENT_DIR' ) ? WP_CONTENT_DIR : ABSPATH . 'wp-content';
		return rtrim( $base_dir, '/' ) . '/cache/lfk-page-cache/' . $cache_key . '.html';
	}
}

if ( ! function_exists( 'lfk_staging_page_cache_send_hit' ) ) {
	function lfk_staging_page_cache_send_hit( $cache_file, $cache_key ) {
		$ttl   = 3600;
		$mtime = @filemtime( $cache_file );
		if ( ! $mtime ) {
			return false;
		}

		$age = time() - $mtime;
		if ( $age < 0 || $age > $ttl ) {
			@unlink( $cache_file );
			return false;
		}

		if ( ! headers_sent() ) {
			header( 'Content-Type: text/html; charset=UTF-8' );
			header( 'Cache-Control: public, max-age=' . max( 0, $ttl - $age ) );
			header( 'X-LFK-Page-Cache: HIT' );
			header( 'X-LFK-Page-Cache-Key: ' . $cache_key );

			$head = file_get_contents( $cache_file, false, null, 0, 32768 );
			if ( $head && preg_match_all( "/<link\\b(?=[^>]*\\brel=[\"']preload[\"'])(?=[^>]*\\bas=[\"']image[\"'])[^>]*\\bhref=[\"']([^\"']+)[\"']/i", $head, $matches ) ) {
				foreach ( array_slice( array_unique( $matches[1] ), 0, 2 ) as $href ) {
					header( 'Link: <' . html_entity_decode( $href, ENT_QUOTES, 'UTF-8' ) . '>; rel=preload; as=image; fetchpriority=high', false );
				}
			}
		}

		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'HEAD' === strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) ) {
			exit;
		}

		readfile( $cache_file );
		exit;
	}
}

if ( ! function_exists( 'lfk_staging_page_cache_store' ) ) {
	function lfk_staging_page_cache_store( $html ) {
		if ( empty( $GLOBALS['lfk_staging_page_cache_key'] ) ) {
			return $html;
		}

		if ( defined( 'DONOTCACHEPAGE' ) && DONOTCACHEPAGE ) {
			return $html;
		}

		if ( function_exists( 'http_response_code' ) && 200 !== http_response_code() ) {
			return $html;
		}

		if ( strlen( $html ) < 2048 || false === stripos( $html, '</html>' ) ) {
			return $html;
		}

		foreach ( headers_list() as $header ) {
			if ( 0 === stripos( $header, 'Content-Type:' ) && false === stripos( $header, 'text/html' ) ) {
				return $html;
			}

			if ( 0 === stripos( $header, 'Set-Cookie:' ) ) {
				return $html;
			}
		}

		$cache_file = lfk_staging_page_cache_file( (string) $GLOBALS['lfk_staging_page_cache_key'] );
		$cache_dir  = dirname( $cache_file );
		if ( ! is_dir( $cache_dir ) ) {
			@mkdir( $cache_dir, 0755, true );
		}

		if ( is_dir( $cache_dir ) && is_writable( $cache_dir ) ) {
			$temp_file = $cache_file . '.' . getmypid() . '.tmp';
			if ( false !== file_put_contents( $temp_file, $html, LOCK_EX ) ) {
				@rename( $temp_file, $cache_file );
			}
		}

		return $html;
	}
}

$lfk_staging_page_cache_key = lfk_staging_page_cache_path_key();
if ( false !== $lfk_staging_page_cache_key ) {
	$GLOBALS['lfk_staging_page_cache_key'] = $lfk_staging_page_cache_key;
	$lfk_staging_page_cache_file           = lfk_staging_page_cache_file( $lfk_staging_page_cache_key );
	if ( is_readable( $lfk_staging_page_cache_file ) ) {
		lfk_staging_page_cache_send_hit( $lfk_staging_page_cache_file, $lfk_staging_page_cache_key );
	}

	if ( ! headers_sent() ) {
		header( 'X-LFK-Page-Cache: MISS' );
		header( 'X-LFK-Page-Cache-Key: ' . $lfk_staging_page_cache_key );
	}
	ob_start( 'lfk_staging_page_cache_store' );
}
