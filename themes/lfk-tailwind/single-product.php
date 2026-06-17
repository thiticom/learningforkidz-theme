<?php

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	global $product;
	$product = wc_get_product( get_the_ID() );

	if ( ! $product ) {
		continue;
	}

	$image_ids = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
	$brand     = get_the_terms( $product->get_id(), 'product_brand' );
	$related   = wc_get_related_products( $product->get_id(), 4 );
	?>
	<main id="primary" class="lfk-single-product">
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

			<?php if ( function_exists( 'woocommerce_output_all_notices' ) ) : ?>
				<?php woocommerce_output_all_notices(); ?>
			<?php endif; ?>

			<div class="lfk-single-grid">
				<section class="lfk-product-gallery" data-lfk-product-gallery>
					<?php if ( $image_ids ) : ?>
						<?php
						$main_id  = reset( $image_ids );
						$main_src = wp_get_attachment_image_url( $main_id, 'woocommerce_single' );
						?>
						<div class="lfk-product-gallery-main">
							<img
								src="<?php echo esc_url( $main_src ); ?>"
								<?php if ( wp_get_attachment_image_srcset( $main_id, 'woocommerce_single' ) ) : ?>
									srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $main_id, 'woocommerce_single' ) ); ?>"
								<?php endif; ?>
								sizes="(max-width: 768px) 100vw, 560px"
								alt="<?php echo esc_attr( $product->get_name() ); ?>"
								data-lfk-product-main
							>
						</div>
						<?php if ( count( $image_ids ) > 1 ) : ?>
							<div class="lfk-product-thumbs">
								<?php foreach ( $image_ids as $index => $image_id ) : ?>
									<button
										class="lfk-product-thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
										type="button"
										data-lfk-product-thumb
										data-image="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) ); ?>"
										data-srcset="<?php echo esc_attr( wp_get_attachment_image_srcset( $image_id, 'woocommerce_single' ) ); ?>"
									>
										<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'loading' => 'eager' ) ); ?>
									</button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					<?php else : ?>
						<div class="lfk-product-gallery-main">
							<img src="<?php echo esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>">
						</div>
					<?php endif; ?>
				</section>

				<section class="lfk-single-summary">
					<h2 class="lfk-single-title"><?php echo esc_html( $product->get_name() ); ?></h2>
					<div class="lfk-single-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>

					<?php echo wp_kses_post( wc_get_stock_html( $product ) ); ?>

					<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
						<div class="lfk-single-cart">
							<?php woocommerce_template_single_add_to_cart(); ?>
						</div>
					<?php endif; ?>

					<a class="lfk-product-wishlist lfk-single-wishlist" href="<?php echo esc_url( home_url( '/wishlists/' ) ); ?>">
						<?php echo lfk_svg_icon( 'heart' ); ?>
						<span>Add to Wishlist</span>
					</a>

					<div class="lfk-product-meta">
						<?php if ( $product->get_sku() ) : ?>
							<div><span><?php esc_html_e( 'SKU:', 'lfk-tailwind' ); ?></span> <?php echo esc_html( $product->get_sku() ); ?></div>
						<?php endif; ?>
						<?php if ( $brand && ! is_wp_error( $brand ) ) : ?>
							<div><span><?php esc_html_e( 'Brand:', 'lfk-tailwind' ); ?></span> <a href="<?php echo esc_url( get_term_link( $brand[0] ) ); ?>"><?php echo esc_html( $brand[0]->name ); ?></a></div>
						<?php endif; ?>
						<div><span><?php esc_html_e( 'Category:', 'lfk-tailwind' ); ?></span> <?php echo wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ' ) ); ?></div>
					</div>
				</section>
			</div>

			<section class="lfk-product-description">
				<h2><?php esc_html_e( 'คำอธิบาย', 'lfk-tailwind' ); ?></h2>
				<div class="lfk-rich-text">
					<?php the_content(); ?>
				</div>
			</section>

			<?php if ( $related ) : ?>
				<section class="lfk-related-products">
					<?php lfk_section_heading( 'สินค้าที่เกี่ยวข้อง' ); ?>
					<div class="lfk-product-grid lfk-archive-grid">
						<?php
						foreach ( $related as $related_id ) {
							lfk_product_card( wc_get_product( $related_id ) );
						}
						?>
					</div>
				</section>
			<?php endif; ?>
		</div>
	</main>
	<?php
endwhile;

get_footer();
