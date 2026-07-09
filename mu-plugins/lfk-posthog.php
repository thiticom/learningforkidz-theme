<?php
/**
 * PostHog tracking for Learning For Kidz.
 *
 * @package LearningForKidz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the PostHog project token.
 *
 * @return string
 */
function lfk_posthog_project_token() {
	return 'phc_tFekxYqG2QysZvVY83AEbfVHUWfnef8ra8KTee2QqJTD';
}

/**
 * Check whether PostHog should load on this request.
 *
 * @return bool
 */
function lfk_posthog_is_enabled() {
	$host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';

	if ( is_admin() || str_starts_with( $host, 'staging.' ) ) {
		return false;
	}

	return '' !== lfk_posthog_project_token();
}

/**
 * Get page context for PostHog event properties.
 *
 * @return array
 */
function lfk_posthog_page_context() {
	$context = array(
		'site_id'      => 'learningforkidz',
		'site_domain'  => 'www.learningforkidz.com',
		'page_type'    => 'other',
		'page_section' => 'site',
	);

	if ( is_front_page() ) {
		$context['page_type']    = 'home';
		$context['page_section'] = 'home';
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$context['page_type']    = 'product_detail';
		$context['page_section'] = 'product';
		$context['content_id']   = get_queried_object_id();
		$context['content_name'] = get_the_title( get_queried_object_id() );
	} elseif ( ( function_exists( 'is_shop' ) && is_shop() ) || is_post_type_archive( 'product' ) ) {
		$context['page_type']    = 'product_archive';
		$context['page_section'] = 'product';
	} elseif ( is_tax( array( 'product_cat', 'product_tag', 'product_brand', 'pa_brand', 'age', 'product_attribute' ) ) ) {
		$term                    = get_queried_object();
		$context['page_type']    = 'product_archive';
		$context['page_section'] = 'product';
		$context['taxonomy']     = $term instanceof WP_Term ? $term->taxonomy : '';
		$context['term_slug']    = $term instanceof WP_Term ? $term->slug : '';
		$context['content_id']   = $term instanceof WP_Term ? $term->term_id : 0;
		$context['content_name'] = $term instanceof WP_Term ? $term->name : '';
	} elseif ( is_search() ) {
		$context['page_type']    = 'search';
		$context['page_section'] = 'search';
		$context['search_term']  = get_search_query( false );
	} elseif ( function_exists( 'is_cart' ) && is_cart() ) {
		$context['page_type']    = 'cart';
		$context['page_section'] = 'checkout';
	} elseif ( function_exists( 'is_checkout' ) && is_checkout() ) {
		$context['page_type']    = function_exists( 'is_order_received_page' ) && is_order_received_page() ? 'order_received' : 'checkout';
		$context['page_section'] = 'checkout';
	} elseif ( function_exists( 'is_account_page' ) && is_account_page() ) {
		$context['page_type']    = 'account';
		$context['page_section'] = 'account';
	} elseif ( is_singular( 'post' ) ) {
		$context['page_type']    = 'blog_post';
		$context['page_section'] = 'blog';
		$context['content_id']   = get_queried_object_id();
		$context['content_name'] = get_the_title( get_queried_object_id() );
	} elseif ( is_page() ) {
		$context['page_type']    = 'page';
		$context['page_section'] = 'page';
		$context['content_id']   = get_queried_object_id();
		$context['content_name'] = get_the_title( get_queried_object_id() );
	}

	return array_filter(
		$context,
		static function ( $value ) {
			return null !== $value && '' !== $value;
		}
	);
}

/**
 * Output the PostHog browser SDK.
 */
function lfk_posthog_output_tag() {
	if ( ! lfk_posthog_is_enabled() ) {
		return;
	}

	$config = array(
		'api_host'                  => 'https://us.i.posthog.com',
		'defaults'                  => '2026-05-30',
		'person_profiles'           => 'identified_only',
		'capture_pageview'          => false,
		'capture_pageleave'         => true,
		'disable_session_recording' => false,
	);
	$context = lfk_posthog_page_context();
	?>
	<script>
		!function(t,e){var o,n,p,r;e.__SV||(window.posthog&&window.posthog.__loaded)||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.crossOrigin="anonymous",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagResult isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
		posthog.init(<?php echo wp_json_encode( lfk_posthog_project_token() ); ?>, <?php echo wp_json_encode( $config ); ?>);
		window.lfkPostHogContext = <?php echo wp_json_encode( $context ); ?>;
		posthog.register(window.lfkPostHogContext);
		posthog.capture('$pageview', Object.assign({
			$current_url: window.location.href
		}, window.lfkPostHogContext));
		posthog.capture('page_context_viewed', window.lfkPostHogContext);
	</script>
	<?php
}
add_action( 'wp_head', 'lfk_posthog_output_tag', 20 );
