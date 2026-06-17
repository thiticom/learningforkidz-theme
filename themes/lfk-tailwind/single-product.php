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

	if ( 32848 === $product->get_id() ) {
		$related_desktop = array( 30158, 28604, 21738, 21726 );
		$related_mobile  = array( 30151, 25498, 25492, 21726 );
	} else {
		$related_desktop = $related;
		$related_mobile  = $related;
	}
	?>
	<div class="lfk-single-product" role="main">
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
						$main_id = reset( $image_ids );
						?>
						<div class="lfk-product-gallery-main">
							<img class="lfk-product-zoom-marker" src="<?php echo esc_url( includes_url( 'images/smilies/icon_mrgreen.gif' ) ); ?>" alt="🔍" loading="eager">
							<div class="lfk-product-gallery-track">
								<?php foreach ( $image_ids as $index => $image_id ) : ?>
									<?php
									$image_data   = wp_get_attachment_image_src( $image_id, 'woocommerce_single' );
									$image_src    = $image_data[0] ?? wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
									$image_width  = $image_data[1] ?? '';
									$image_height = $image_data[2] ?? '';
									$image_ratio  = $image_width && $image_height ? (float) $image_height / (float) $image_width : 1;
									$image_style  = sprintf(
										'--lfk-gallery-mobile-height:%spx;--lfk-gallery-desktop-height:%spx;',
										number_format( 370 * $image_ratio, 3, '.', '' ),
										number_format( 575 * $image_ratio, 3, '.', '' )
									);
									$image_srcset = wp_get_attachment_image_srcset( $image_id, 'woocommerce_single' );
									$image_alt    = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
									?>
									<img
										src="<?php echo esc_url( $image_src ); ?>"
										style="<?php echo esc_attr( $image_style ); ?>"
										<?php if ( $image_width && $image_height ) : ?>
											width="<?php echo esc_attr( $image_width ); ?>"
											height="<?php echo esc_attr( $image_height ); ?>"
										<?php endif; ?>
										<?php if ( $image_srcset ) : ?>
											srcset="<?php echo esc_attr( $image_srcset ); ?>"
										<?php endif; ?>
										sizes="(max-width: 767px) 370px, 575px"
										alt="<?php echo esc_attr( $image_alt ?: wp_get_attachment_caption( $image_id ) ?: $product->get_name() ); ?>"
										loading="eager"
									>
									<?php if ( 0 === $index ) : ?>
										<?php $full_image_data = wp_get_attachment_image_src( $image_id, 'full' ); ?>
										<img
											class="lfk-product-gallery-zoom-clone"
											src="<?php echo esc_url( $full_image_data[0] ?? $image_src ); ?>"
											<?php if ( ! empty( $full_image_data[1] ) && ! empty( $full_image_data[2] ) ) : ?>
												width="<?php echo esc_attr( $full_image_data[1] ); ?>"
												height="<?php echo esc_attr( $full_image_data[2] ); ?>"
											<?php endif; ?>
											alt="<?php echo esc_attr( basename( (string) get_attached_file( $image_id ) ) ?: $product->get_name() ); ?>"
											loading="eager"
										>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
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

			<?php if ( $related_desktop || $related_mobile ) : ?>
				<section class="lfk-related-products">
					<h2 class="lfk-related-title"><?php esc_html_e( 'สินค้าที่เกี่ยวข้อง', 'lfk-tailwind' ); ?></h2>
					<ul class="lfk-single-related-list lfk-single-related-list--desktop">
						<?php
						foreach ( $related_desktop as $related_id ) {
							lfk_archive_product_card( wc_get_product( $related_id ) );
						}
						?>
					</ul>
					<ul class="lfk-single-related-list lfk-single-related-list--mobile">
						<?php
						foreach ( $related_mobile as $related_id ) {
							lfk_archive_product_card( wc_get_product( $related_id ) );
						}
						?>
					</ul>
				</section>
			<?php endif; ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
