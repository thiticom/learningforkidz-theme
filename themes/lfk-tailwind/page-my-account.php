<?php

get_header();
?>
<main id="primary" class="lfk-woocommerce-page lfk-account-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php esc_html_e( 'บัญชีของฉัน', 'lfk-tailwind' ); ?></h1>
		</header>
		<div class="lfk-woocommerce-content">
			<?php echo do_shortcode( '[woocommerce_my_account]' ); ?>
		</div>
	</div>
</main>
<?php
get_footer();
