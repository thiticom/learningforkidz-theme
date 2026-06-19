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
$child_terms   = array();

if ( is_tax( 'product_cat' ) ) {
	$queried_term = get_queried_object();
	if ( $queried_term instanceof WP_Term ) {
		$child_terms = get_terms( array(
			'taxonomy'   => 'product_cat',
			'parent'     => $queried_term->term_id,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );
		if ( is_wp_error( $child_terms ) ) {
			$child_terms = array();
		} else {
			$child_terms = array_values( $child_terms );
		}
	}
}
?>
<div class="lfk-product-archive<?php echo $hero_image_id ? ' lfk-product-archive--has-hero' : ''; ?>" role="main" data-lfk-archive>
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

		<?php if ( $description ) : ?>
			<div class="lfk-archive-intro" aria-label="<?php echo esc_attr( $archive_title ); ?>">
				<div class="lfk-archive-description"><?php echo wp_kses_post( wpautop( $description ) ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( function_exists( 'woocommerce_output_all_notices' ) ) : ?>
			<?php woocommerce_output_all_notices(); ?>
		<?php endif; ?>

		<?php if ( $child_terms ) : ?>
			<section class="lfk-category-archive-grid" aria-label="<?php echo esc_attr( $archive_title ); ?>">
				<?php foreach ( $child_terms as $index => $term ) : ?>
					<?php
					$thumbnail_id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
					$term_link    = get_term_link( $term );
					if ( is_wp_error( $term_link ) ) {
						continue;
					}
					?>
					<a class="lfk-category-card" href="<?php echo esc_url( $term_link ); ?>">
						<?php if ( $thumbnail_id ) : ?>
							<?php
							echo lfk_local_attachment_image(
								$thumbnail_id,
								'medium_large',
								array(
									'loading'       => 0 === $index ? 'eager' : 'lazy',
									'fetchpriority' => 0 === $index ? 'high' : 'low',
								)
							);
							?>
						<?php else : ?>
							<span class="lfk-category-card-fallback"><?php echo esc_html( $term->name ); ?></span>
						<?php endif; ?>
						<h2><?php echo esc_html( $term->name ); ?></h2>
					</a>
				<?php endforeach; ?>
			</section>

		<?php elseif ( have_posts() ) : ?>
			<div class="lfk-archive-layout">
				<details class="lfk-mobile-filters">
					<summary><?php esc_html_e( 'กรอง', 'lfk-tailwind' ); ?></summary>
					<div class="lfk-mobile-filters-panel">
						<form class="lfk-filter-form" method="get" action="<?php echo esc_url( lfk_archive_filter_action_url() ); ?>" data-lfk-filter-form>
							<?php
							lfk_archive_filter_hidden_inputs();
							lfk_archive_filter_terms( 'product_brand', __( 'กรองตามยี่ห้อ', 'lfk-tailwind' ) );
							lfk_archive_filter_terms( 'age', __( 'กรองตามอายุ', 'lfk-tailwind' ) );
							lfk_archive_price_filters();
							?>
							<noscript><button class="lfk-filter-submit" type="submit"><?php esc_html_e( 'ใช้ตัวกรอง', 'lfk-tailwind' ); ?></button></noscript>
						</form>
					</div>
				</details>
				<aside class="lfk-archive-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'lfk-tailwind' ); ?>">
					<div class="lfk-sidebar-title"><?php esc_html_e( 'กรอง', 'lfk-tailwind' ); ?></div>
					<form class="lfk-filter-form" method="get" action="<?php echo esc_url( lfk_archive_filter_action_url() ); ?>" data-lfk-filter-form>
						<?php
						lfk_archive_filter_hidden_inputs();
						lfk_archive_filter_terms( 'product_brand', __( 'กรองตามยี่ห้อ', 'lfk-tailwind' ) );
						lfk_archive_filter_terms( 'age', __( 'กรองตามอายุ', 'lfk-tailwind' ) );
						lfk_archive_price_filters();
						?>
						<noscript><button class="lfk-filter-submit" type="submit"><?php esc_html_e( 'ใช้ตัวกรอง', 'lfk-tailwind' ); ?></button></noscript>
					</form>
				</aside>

				<section class="lfk-archive-products" aria-label="<?php echo esc_attr( $archive_title ); ?>">
					<?php if ( function_exists( 'woocommerce_catalog_ordering' ) ) : ?>
						<div class="lfk-archive-ordering">
							<?php woocommerce_catalog_ordering(); ?>
						</div>
					<?php endif; ?>

					<ul class="lfk-archive-product-list">
						<?php
						while ( have_posts() ) :
							the_post();
							lfk_archive_product_card( wc_get_product( get_the_ID() ) );
						endwhile;
						?>
					</ul>
					<div class="lfk-archive-load-status" role="status" aria-live="polite" data-lfk-archive-status></div>
					<?php if ( function_exists( 'woocommerce_pagination' ) ) : ?>
						<nav class="lfk-pagination" aria-label="<?php esc_attr_e( 'Product pagination', 'lfk-tailwind' ); ?>" data-lfk-pagination>
							<?php woocommerce_pagination(); ?>
						</nav>
					<?php endif; ?>
				</section>
			</div>

		<?php else : ?>
			<div class="lfk-empty-state">
				<?php do_action( 'woocommerce_no_products_found' ); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
if ( function_exists( 'wc_reset_loop' ) ) {
	wc_reset_loop();
}
