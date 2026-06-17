<?php

get_header();
?>
<?php
while ( have_posts() ) :
	the_post();

	if ( trim( get_the_content() ) ) {
		?>
		<div class="lfk-checkout-page">
			<?php
			the_content();
			?>
		</div>
		<?php
	} else {
		?>
		<main id="primary" class="lfk-woocommerce-page lfk-checkout-page">
			<div class="lfk-shell">
				<header class="lfk-post-archive-header">
					<h1><?php esc_html_e( 'สั่งซื้อและชำระเงิน', 'lfk-tailwind' ); ?></h1>
				</header>
				<div class="lfk-woocommerce-content">
					<?php echo do_shortcode( '[woocommerce_checkout]' ); ?>
				</div>
			</div>
		</main>
		<?php
	}
endwhile;
?>
<?php
get_footer();
