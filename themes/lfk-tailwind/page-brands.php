<?php

get_header();

$brands = get_terms( array(
	'taxonomy'   => 'product_brand',
	'hide_empty' => false,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );
?>
<main id="primary" class="lfk-directory-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php esc_html_e( 'Brands', 'lfk-tailwind' ); ?></h1>
		</header>

		<?php if ( $brands && ! is_wp_error( $brands ) ) : ?>
			<div class="lfk-term-grid">
				<?php foreach ( $brands as $brand ) : ?>
					<?php $thumbnail = function_exists( 'wc_get_brand_thumbnail_url' ) ? wc_get_brand_thumbnail_url( $brand->term_id, 'medium' ) : ''; ?>
					<a class="lfk-term-card" href="<?php echo esc_url( get_term_link( $brand ) ); ?>">
						<span class="lfk-term-art">
							<?php if ( $thumbnail ) : ?>
								<img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $brand->name ); ?>" loading="lazy">
							<?php else : ?>
								<img src="<?php echo esc_url( lfk_logo_url() ); ?>" alt="" loading="lazy">
							<?php endif; ?>
						</span>
						<span class="lfk-term-title"><?php echo esc_html( $brand->name ); ?></span>
						<span class="lfk-term-count"><?php echo esc_html( sprintf( _n( '%d product', '%d products', (int) $brand->count, 'lfk-tailwind' ), (int) $brand->count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
