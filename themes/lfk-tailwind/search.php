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
?>
<main id="primary" class="lfk-search-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php echo esc_html( sprintf( __( 'Search: %s', 'lfk-tailwind' ), $query ) ); ?></h1>
			<form class="lfk-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>" method="get">
				<input class="lfk-search-input" type="search" name="s" value="<?php echo esc_attr( $query ); ?>" placeholder="<?php esc_attr_e( 'Search products and articles', 'lfk-tailwind' ); ?>">
				<button class="lfk-search-submit" type="submit"><?php esc_html_e( 'Search', 'lfk-tailwind' ); ?></button>
			</form>
		</header>

		<?php if ( $product_results ) : ?>
			<section class="lfk-search-section">
				<h2><?php esc_html_e( 'Products', 'lfk-tailwind' ); ?></h2>
				<div class="lfk-product-grid lfk-archive-grid">
					<?php
					foreach ( $product_results as $product_id ) {
						lfk_product_card( wc_get_product( $product_id ) );
					}
					?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( $content_results ) : ?>
			<section class="lfk-search-section">
				<h2><?php esc_html_e( 'Articles and Pages', 'lfk-tailwind' ); ?></h2>
				<div class="lfk-article-grid lfk-post-grid">
					<?php foreach ( $content_results as $index => $post ) : ?>
						<?php lfk_article_card( $post, $index ); ?>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>

		<?php if ( ! $product_results && ! $content_results ) : ?>
			<div class="lfk-empty-state">
				<p><?php esc_html_e( 'No results found. Try another keyword or browse the shop.', 'lfk-tailwind' ); ?></p>
				<a class="lfk-search-submit" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Go to shop', 'lfk-tailwind' ); ?></a>
			</div>
		<?php endif; ?>

		<div class="lfk-pagination">
			<?php the_posts_pagination(); ?>
		</div>
	</div>
</main>
<?php
get_footer();
