<?php

get_header();

$query = get_search_query();
$product_results = array();
$content_results = array();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		if ( 'product' === get_post_type() ) {
			$product_results[] = get_the_ID();
		} else {
			$content_results[] = get_post();
		}
	}
}

$has_results = $product_results || $content_results;
?>
<main id="primary" class="lfk-search-page">
	<div class="lfk-shell">
		<header class="lfk-search-header">
			<h4><?php echo esc_html( sprintf( __( 'Search Results for: %s', 'lfk-tailwind' ), $query ) ); ?></h4>
		</header>

		<?php if ( $product_results ) : ?>
			<ul class="lfk-search-grid lfk-search-product-grid">
				<?php
				foreach ( $product_results as $index => $product_id ) {
					$product = function_exists( 'wc_get_product' ) ? wc_get_product( $product_id ) : null;
					if ( ! $product ) {
						continue;
					}

					lfk_archive_product_card(
						$product,
						array(
							'loading'       => $index < 4 ? 'eager' : 'lazy',
							'fetchpriority' => 0 === $index ? 'high' : false,
						)
					);
				}
				?>
			</ul>
		<?php endif; ?>

		<?php if ( $content_results ) : ?>
			<div class="lfk-article-grid lfk-search-grid lfk-search-content-grid">
				<?php
				foreach ( $content_results as $index => $result ) {
					lfk_article_card( $result, $index + count( $product_results ) );
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( ! $has_results ) : ?>
			<div class="lfk-empty-state">
				<p><?php esc_html_e( 'No results found. Try another keyword or browse the shop.', 'lfk-tailwind' ); ?></p>
				<a class="lfk-search-submit" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Go to shop', 'lfk-tailwind' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
