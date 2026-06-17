<?php

get_header();

while ( have_posts() ) :
	the_post();
	$content = get_the_content( null, false, get_the_ID() );
	$title   = __( 'รายการสิ่งที่ปรารถนา', 'lfk-tailwind' );
	if ( preg_match( '/<h2\b[^>]*>(.*?)<\/h2>/is', $content, $title_match ) ) {
		$title = wp_strip_all_tags( $title_match[1] );
	}
?>
<main id="primary" class="lfk-woocommerce-page lfk-wishlist-page">
	<div class="lfk-shell">
		<div class="lfk-wishlist-content">
			<h2><?php echo esc_html( $title ); ?></h2>
			<?php echo wp_kses_post( do_shortcode( '[wishlist_page]' ) ); ?>
		</div>
	</div>
</main>
<?php
endwhile;

get_footer();
