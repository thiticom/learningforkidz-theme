<?php

defined( 'ABSPATH' ) || exit;

global $wp_query;

if ( function_exists( 'wc_setup_loop' ) ) {
	wc_setup_loop( array(
		'total'        => isset( $wp_query->found_posts ) ? (int) $wp_query->found_posts : 0,
		'total_pages'  => isset( $wp_query->max_num_pages ) ? (int) $wp_query->max_num_pages : 0,
		'per_page'     => (int) $wp_query->get( 'posts_per_page' ),
		'current_page' => max( 1, (int) get_query_var( 'paged' ) ),
		'is_paginated' => isset( $wp_query->max_num_pages ) && $wp_query->max_num_pages > 1,
	) );
}

$archive_title = function_exists( 'woocommerce_page_title' ) ? woocommerce_page_title( false ) : get_the_archive_title();
$description   = is_tax() ? term_description() : '';
$hero_image_id = function_exists( 'lfk_product_archive_hero_image_id' ) ? lfk_product_archive_hero_image_id() : 0;
?>
<main id="primary" class="lfk-product-archive">
	<div class="lfk-shell">
		<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
			<div class="lfk-breadcrumbs">
				<?php
				woocommerce_breadcrumb( array(
					'delimiter'   => '<span>/</span>',
					'wrap_before' => '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__( 'Breadcrumb', 'lfk-tailwind' ) . '">',
					'wrap_after'  => '</nav>',
				) );
				?>
			</div>
		<?php endif; ?>

		<?php if ( $hero_image_id ) : ?>
			<div class="lfk-archive-hero">
				<?php echo wp_get_attachment_image( $hero_image_id, 'full', false, array( 'loading' => 'eager' ) ); ?>
			</div>
		<?php endif; ?>

		<header class="lfk-archive-header">
			<div class="lfk-archive-copy">
				<h1 class="lfk-archive-title"><?php echo esc_html( $archive_title ); ?></h1>
				<?php if ( $description ) : ?>
					<div class="lfk-archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
				<?php endif; ?>
				<?php if ( lfk_product_archive_result_count() ) : ?>
					<p class="lfk-result-count"><?php echo esc_html( lfk_product_archive_result_count() ); ?></p>
				<?php endif; ?>
			</div>

			<?php if ( function_exists( 'woocommerce_catalog_ordering' ) ) : ?>
				<div class="lfk-archive-ordering">
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			<?php endif; ?>
		</header>

		<?php if ( function_exists( 'woocommerce_output_all_notices' ) ) : ?>
			<?php woocommerce_output_all_notices(); ?>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="lfk-archive-layout">
				<aside class="lfk-archive-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'lfk-tailwind' ); ?>">
					<h2><?php esc_html_e( 'กรอง', 'lfk-tailwind' ); ?></h2>
					<?php
					lfk_archive_filter_terms( 'product_brand', __( 'กรองตามยี่ห้อ', 'lfk-tailwind' ) );
					lfk_archive_filter_terms( 'age', __( 'กรองตามอายุ', 'lfk-tailwind' ) );
					lfk_archive_price_filters();
					?>
				</aside>

				<div class="lfk-archive-products">
					<div class="lfk-product-grid lfk-archive-grid">
						<?php
						while ( have_posts() ) :
							the_post();
							lfk_product_card( wc_get_product( get_the_ID() ) );
						endwhile;
						?>
					</div>
				</div>
			</div>

			<?php if ( function_exists( 'woocommerce_pagination' ) ) : ?>
				<div class="lfk-pagination">
					<?php woocommerce_pagination(); ?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="lfk-empty-state">
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
if ( function_exists( 'wc_reset_loop' ) ) {
	wc_reset_loop();
}
