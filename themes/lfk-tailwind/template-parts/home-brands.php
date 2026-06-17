<?php
$brands = array(
	array( 'href' => '/product-category/brand/learning-resources/', 'src' => '2022/09/NBR-LR-Logo_CMYK-2in-r.webp', 'alt' => 'Learning Resources' ),
	array( 'href' => '/product-category/brand/educational-insights/', 'src' => '2022/09/EI_Logo_Horizontal_Blue-1-r.webp', 'alt' => 'Educational Insights' ),
	array( 'href' => '/product-category/brand/viga/', 'src' => '2022/09/Viga-r.webp', 'alt' => 'Viga' ),
	array( 'href' => '/product-category/brand/hand2mind/', 'src' => '2023/09/hand2mind.webp', 'alt' => 'hand2mind' ),
	array( 'href' => '/product-category/brand/learning-mats/', 'src' => '2023/09/learningmats-1.jpg', 'alt' => 'Learning Mats' ),
	array( 'href' => '/product-category/brand/ching-ching/', 'src' => '2022/09/chingChing-logo.webp', 'alt' => 'Ching Ching' ),
	array( 'href' => '/product-category/brand/beleduc/', 'src' => '2022/09/Beleduc-logo.webp', 'alt' => 'Beleduc' ),
	array( 'href' => '/product-category/brand/creative-spot/', 'src' => '2022/08/CreativeSpot.jpg', 'alt' => 'Creative Spot' ),
	array( 'href' => '/product-category/brand/botley/', 'src' => '2023/09/Botley2_logo.jpg', 'alt' => 'Botley' ),
	array( 'href' => '/product-category/brand/top-bright/', 'src' => '2022/09/1024px-Topbright_Logo-r.webp', 'alt' => 'Topbright' ),
	array( 'href' => '/product-category/brand/zzzmoon/', 'src' => '2023/09/ZZzMoon-logo.webp', 'alt' => 'ZZzMoon' ),
	array( 'href' => '/product-category/brand/science-can/', 'src' => '2023/09/Science-Can-logo.webp', 'alt' => 'Science Can' ),
);
?>
<section class="lfk-brands-section">
	<div class="lfk-shell">
		<?php lfk_section_heading( 'แบรนด์ที่เราไว้ใจ' ); ?>
		<div class="lfk-carousel" data-lfk-carousel>
			<button class="lfk-carousel-arrow lfk-carousel-prev" type="button" aria-label="<?php esc_attr_e( 'Previous brands', 'lfk-tailwind' ); ?>" data-lfk-carousel-prev><?php echo lfk_svg_icon( 'chevron-left' ); ?></button>
			<div class="lfk-carousel-viewport">
				<div class="lfk-logo-track" data-lfk-carousel-track>
					<?php foreach ( $brands as $brand ) : ?>
						<a class="lfk-brand-logo" href="<?php echo esc_url( home_url( $brand['href'] ) ); ?>">
							<img src="<?php echo esc_url( lfk_remote_upload_url( $brand['src'] ) ); ?>" alt="<?php echo esc_attr( $brand['alt'] ); ?>" loading="lazy">
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<button class="lfk-carousel-arrow lfk-carousel-next" type="button" aria-label="<?php esc_attr_e( 'Next brands', 'lfk-tailwind' ); ?>" data-lfk-carousel-next><?php echo lfk_svg_icon( 'chevron-right' ); ?></button>
		</div>
	</div>
</section>

<section class="lfk-order-strip">
	<div class="lfk-shell">
		<img class="lfk-wide-image" src="<?php echo esc_url( lfk_remote_upload_url( '2023/09/howtoorder-4.png' ) ); ?>" alt="วิธีการสั่งซื้อ" width="1900" height="332" loading="lazy">
	</div>
</section>
