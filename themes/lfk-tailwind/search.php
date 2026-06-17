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

$ordered_results = array_merge(
	array_slice( $product_results, 0, 3 ),
	$content_results,
	array_slice( $product_results, 3 )
);
?>
<main id="primary" class="lfk-search-page">
	<div class="lfk-shell">
		<header class="lfk-search-header">
			<h4><?php echo esc_html( sprintf( __( 'Search Results for: %s', 'lfk-tailwind' ), $query ) ); ?></h4>
		</header>

		<?php if ( $ordered_results ) : ?>
			<div class="lfk-article-grid lfk-search-grid">
				<?php
				foreach ( $ordered_results as $index => $result ) {
					lfk_article_card( $result, $index );
				}
				?>
			</div>
		<?php endif; ?>

		<?php if ( ! $ordered_results ) : ?>
			<div class="lfk-empty-state">
				<p><?php esc_html_e( 'No results found. Try another keyword or browse the shop.', 'lfk-tailwind' ); ?></p>
				<a class="lfk-search-submit" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Go to shop', 'lfk-tailwind' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
