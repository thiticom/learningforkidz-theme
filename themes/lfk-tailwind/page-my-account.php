<?php

get_header();
?>
<main id="primary" class="lfk-woocommerce-page lfk-account-page">
	<div class="lfk-shell">
		<header class="lfk-account-header">
			<h2><?php esc_html_e( 'บัญชีของฉัน', 'lfk-tailwind' ); ?></h2>
		</header>
		<div class="lfk-woocommerce-content">
			<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
		</div>
	</div>
</main>
<?php
get_footer();
