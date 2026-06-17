<?php

get_header();

$paged = max( 1, (int) get_query_var( 'paged' ) );
$promotion_products = new WP_Query( array(
	'post_type'      => 'product',
	'post_status'    => 'publish',
	'posts_per_page' => 12,
	'paged'          => $paged,
	'tax_query'      => array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => 'promotion',
		),
	),
) );
?>
<main id="primary" class="lfk-product-archive lfk-promotion-page">
	<div class="lfk-shell">
		<header class="lfk-archive-header">
			<div class="lfk-archive-copy">
				<h1 class="lfk-archive-title"><?php esc_html_e( 'Promotion', 'lfk-tailwind' ); ?></h1>
				<p class="lfk-result-count"><?php echo esc_html( sprintf( _n( '%d product', '%d products', (int) $promotion_products->found_posts, 'lfk-tailwind' ), (int) $promotion_products->found_posts ) ); ?></p>
			</div>
		</header>

		<?php if ( $promotion_products->have_posts() ) : ?>
			<div class="lfk-product-grid lfk-archive-grid">
				<?php
				while ( $promotion_products->have_posts() ) :
					$promotion_products->the_post();
					lfk_product_card( wc_get_product( get_the_ID() ) );
				endwhile;
				?>
			</div>
			<div class="lfk-pagination">
				<?php
				echo paginate_links( array(
					'total'   => max( 1, (int) $promotion_products->max_num_pages ),
					'current' => $paged,
				) );
				?>
			</div>
		<?php else : ?>
			<div class="lfk-empty-state"><?php esc_html_e( 'No promotions right now.', 'lfk-tailwind' ); ?></div>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
</main>
<?php
get_footer();
