<?php

get_header();
?>
<main id="primary" class="lfk-woocommerce-page lfk-wishlist-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php esc_html_e( 'รายการสิ่งที่ปรารถนา', 'lfk-tailwind' ); ?></h1>
		</header>
		<div class="lfk-woocommerce-content">
			<?php echo do_shortcode( '[wishlist_page]' ); ?>
		</div>
	</div>
</main>
<?php
get_footer();
