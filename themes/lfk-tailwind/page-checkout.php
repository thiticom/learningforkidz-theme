<?php

get_header();
?>
<main id="primary" class="lfk-woocommerce-page lfk-checkout-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h2 class="lfk-checkout-title"><?php esc_html_e( 'สั่งซื้อและชำระเงิน', 'lfk-tailwind' ); ?></h2>
		</header>
		<div class="lfk-woocommerce-content">
			<?php echo do_shortcode( '[woocommerce_checkout]' ); ?>
		</div>
	</div>
</main>
<?php
get_footer();
