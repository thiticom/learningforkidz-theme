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

	$image_ids     = array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) );
	$brand         = get_the_terms( $product->get_id(), 'product_brand' );
	$related       = wc_get_related_products( $product->get_id(), 4 );
	$gallery_items = array();

	foreach ( $image_ids as $image_id ) {
		$image_data   = wp_get_attachment_image_src( $image_id, 'woocommerce_single' );
		$image_src    = $image_data[0] ?? wp_get_attachment_image_url( $image_id, 'woocommerce_single' );
		$image_width  = $image_data[1] ?? '';
		$image_height = $image_data[2] ?? '';
		$image_ratio  = $image_width && $image_height ? (float) $image_height / (float) $image_width : 1;

		$gallery_items[] = array(
			'id'             => $image_id,
			'src'            => $image_src,
			'width'          => $image_width,
			'height'         => $image_height,
			'mobile_height'  => number_format( 370 * $image_ratio, 3, '.', '' ),
			'desktop_height' => number_format( 575 * $image_ratio, 3, '.', '' ),
			'srcset'         => wp_get_attachment_image_srcset( $image_id, 'woocommerce_single' ),
			'alt'            => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
		);
	}

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
					<?php if ( $gallery_items ) : ?>
						<?php
						$main_item     = reset( $gallery_items );
						$gallery_style = sprintf(
							'--lfk-gallery-current-mobile-height:%spx;--lfk-gallery-current-desktop-height:%spx;',
							$main_item['mobile_height'],
							$main_item['desktop_height']
						);
						?>
						<div class="lfk-product-gallery-main" style="<?php echo esc_attr( $gallery_style ); ?>">
							<img class="lfk-product-zoom-marker" src="<?php echo esc_url( includes_url( 'images/smilies/icon_mrgreen.gif' ) ); ?>" alt="🔍" loading="eager">
							<div class="lfk-product-gallery-track">
								<?php foreach ( $gallery_items as $index => $gallery_item ) : ?>
									<?php
									$image_id    = $gallery_item['id'];
									$image_style = sprintf(
										'--lfk-gallery-mobile-height:%spx;--lfk-gallery-desktop-height:%spx;',
										$gallery_item['mobile_height'],
										$gallery_item['desktop_height']
									);
									?>
									<img
										src="<?php echo esc_url( $gallery_item['src'] ); ?>"
										style="<?php echo esc_attr( $image_style ); ?>"
										<?php if ( $gallery_item['width'] && $gallery_item['height'] ) : ?>
											width="<?php echo esc_attr( $gallery_item['width'] ); ?>"
											height="<?php echo esc_attr( $gallery_item['height'] ); ?>"
										<?php endif; ?>
										<?php if ( $gallery_item['srcset'] ) : ?>
											srcset="<?php echo esc_attr( $gallery_item['srcset'] ); ?>"
										<?php endif; ?>
										sizes="(max-width: 767px) 370px, 575px"
										alt="<?php echo esc_attr( $gallery_item['alt'] ?: wp_get_attachment_caption( $image_id ) ?: $product->get_name() ); ?>"
										loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
										fetchpriority="<?php echo 0 === $index ? 'high' : 'low'; ?>"
									>
									<?php if ( 0 === $index ) : ?>
										<?php $full_image_data = wp_get_attachment_image_src( $image_id, 'full' ); ?>
										<img
											class="lfk-product-gallery-zoom-clone"
											src="data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%221%22 height=%221%22 viewBox=%220 0 1 1%22%3E%3C/svg%3E"
											data-src="<?php echo esc_url( $full_image_data[0] ?? $gallery_item['src'] ); ?>"
											<?php if ( ! empty( $full_image_data[1] ) && ! empty( $full_image_data[2] ) ) : ?>
												width="<?php echo esc_attr( $full_image_data[1] ); ?>"
												height="<?php echo esc_attr( $full_image_data[2] ); ?>"
											<?php endif; ?>
											alt="<?php echo esc_attr( basename( (string) get_attached_file( $image_id ) ) ?: $product->get_name() ); ?>"
											loading="lazy"
										>
									<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
						<?php if ( count( $gallery_items ) > 1 ) : ?>
							<div class="lfk-product-thumbs">
								<?php foreach ( $gallery_items as $index => $gallery_item ) : ?>
									<?php $image_id = $gallery_item['id']; ?>
									<button
										class="lfk-product-thumb<?php echo 0 === $index ? ' is-active' : ''; ?>"
										type="button"
										data-lfk-product-thumb
										data-image="<?php echo esc_url( $gallery_item['src'] ); ?>"
										data-srcset="<?php echo esc_attr( $gallery_item['srcset'] ); ?>"
										data-mobile-height="<?php echo esc_attr( $gallery_item['mobile_height'] ); ?>"
										data-desktop-height="<?php echo esc_attr( $gallery_item['desktop_height'] ); ?>"
									>
										<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'loading' => 0 === $index ? 'eager' : 'lazy' ) ); ?>
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

					<div class="lfk-product-meta">
						<?php if ( $product->get_sku() ) : ?>
							<div><span><?php esc_html_e( 'SKU:', 'lfk-tailwind' ); ?></span> <?php echo esc_html( $product->get_sku() ); ?></div>
						<?php endif; ?>
						<div><span><?php esc_html_e( 'Categories:', 'lfk-tailwind' ); ?></span> <?php echo wp_kses_post( wc_get_product_category_list( $product->get_id(), ', ' ) ); ?></div>
						<?php if ( $brand && ! is_wp_error( $brand ) ) : ?>
							<div><span><?php esc_html_e( 'แบรนด์:', 'lfk-tailwind' ); ?></span> <a href="<?php echo esc_url( get_term_link( $brand[0] ) ); ?>"><?php echo esc_html( $brand[0]->name ); ?></a></div>
						<?php endif; ?>
					</div>
				</section>
			</div>

			<section class="lfk-product-description">
				<div class="lfk-product-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Product information', 'lfk-tailwind' ); ?>">
					<span class="lfk-product-tab is-active" role="tab" aria-selected="true"><?php esc_html_e( 'คำอธิบาย', 'lfk-tailwind' ); ?></span>
					<?php if ( $product->has_attributes() || $product->has_dimensions() || $product->has_weight() ) : ?>
						<span class="lfk-product-tab" role="tab" aria-selected="false"><?php esc_html_e( 'ข้อมูลเพิ่มเติม', 'lfk-tailwind' ); ?></span>
					<?php endif; ?>
					<span class="lfk-product-tab" role="tab" aria-selected="false">
						<?php
						printf(
							/* translators: %d: product review count. */
							esc_html__( 'บทวิจารณ์ (%d)', 'lfk-tailwind' ),
							(int) get_comments_number()
						);
						?>
					</span>
				</div>
				<div class="lfk-product-description-panel">
					<h2><?php esc_html_e( 'คำอธิบาย', 'lfk-tailwind' ); ?></h2>
					<div class="lfk-rich-text">
						<?php the_content(); ?>
					</div>
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
