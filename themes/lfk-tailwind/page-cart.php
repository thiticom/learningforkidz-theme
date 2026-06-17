<?php

get_header();
?>
<main id="primary" class="lfk-woocommerce-page lfk-cart-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h2 class="lfk-cart-title"><?php esc_html_e( 'ตะกร้าสินค้า', 'lfk-tailwind' ); ?></h2>
		</header>
		<div class="lfk-woocommerce-content">
			<?php echo do_shortcode( '[woocommerce_cart]' ); ?>
		</div>
	</div>
</main>
<?php
get_footer();
