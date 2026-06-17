<?php

get_header();
?>
<main id="primary" class="lfk-error-page">
	<div class="lfk-shell">
		<section class="lfk-error-panel">
			<h1><?php esc_html_e( 'Page not found', 'lfk-tailwind' ); ?></h1>
			<p><?php esc_html_e( 'The page may have moved. Search again or continue to the shop.', 'lfk-tailwind' ); ?></p>
			<form class="lfk-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
				<input class="lfk-search-input" type="search" name="s" placeholder="<?php esc_attr_e( 'Search products and articles', 'lfk-tailwind' ); ?>">
				<button class="lfk-search-submit" type="submit"><?php esc_html_e( 'Search', 'lfk-tailwind' ); ?></button>
			</form>
			<div class="lfk-error-actions">
				<a class="lfk-search-submit" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'lfk-tailwind' ); ?></a>
				<a class="lfk-search-submit" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop', 'lfk-tailwind' ); ?></a>
			</div>
		</section>
	</div>
</main>
<?php
get_footer();
