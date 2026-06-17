<?php
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

$new_products = new WP_Query( array(
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'posts_per_page'      => 4,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
) );

$featured_products = new WP_Query( array(
	'post_type'           => 'product',
	'post_status'         => 'publish',
	'posts_per_page'      => 4,
	'orderby'             => array(
		'menu_order' => 'ASC',
		'date'       => 'DESC',
	),
	'ignore_sticky_posts' => true,
	'tax_query'           => array(
		array(
			'taxonomy' => 'product_visibility',
			'field'    => 'slug',
			'terms'    => 'featured',
		),
	),
) );

$sections = array(
	array( 'title' => 'สินค้ามาใหม่', 'query' => $new_products ),
	array( 'title' => 'สินค้าแนะนำ', 'query' => $featured_products ),
);
?>
<?php foreach ( $sections as $section ) : ?>
	<?php if ( $section['query']->have_posts() ) : ?>
		<section class="lfk-products-section">
			<div class="lfk-shell">
				<?php lfk_section_heading( $section['title'] ); ?>
				<div class="lfk-product-grid">
					<?php
					while ( $section['query']->have_posts() ) :
						$section['query']->the_post();
						lfk_product_card( wc_get_product( get_the_ID() ) );
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</div>
		</section>
	<?php endif; ?>
<?php endforeach; ?>

<section class="lfk-promo-banner">
	<div class="lfk-shell">
		<img class="lfk-wide-image" src="<?php echo esc_url( lfk_remote_upload_url( '2023/09/ของเล่นเสริมทักษะคณิตศาสตร์สุดฮิต-จากการ.png' ) ); ?>" alt="ของเล่นเสริมทักษะคณิตศาสตร์สุดฮิต" width="1200" height="300" loading="lazy">
	</div>
</section>
