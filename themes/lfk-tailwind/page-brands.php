<?php

get_header();

$brand_cards = array(
	array(
		'slug'  => 'alldoro',
		'image' => '2025/07/alldoro_Logo_new.jpg',
		'alt'   => 'alldoro_Logo_new',
	),
	array(
		'slug'  => 'beleduc',
		'image' => '2025/07/Beleduc.jpg',
		'alt'   => 'Beleduc',
	),
	array(
		'slug'  => 'educational-insights',
		'image' => '2025/07/EI_Logo_2022_BLUE_Large.jpg',
		'alt'   => 'EI_Logo_2022_BLUE_Large',
	),
	array(
		'slug'  => 'hand2mind',
		'image' => '2025/07/hand2mind_4c.jpg',
		'alt'   => 'hand2mind_4c',
	),
	array(
		'slug'  => 'joy-n-play',
		'image' => '2025/07/joyNplay-scaled-1.jpg',
		'alt'   => 'joyNplay-scaled',
	),
	array(
		'slug'  => 'learning-mats',
		'image' => '2025/07/logo-learning-mats.jpg',
		'alt'   => 'logo-learning-mats',
	),
	array(
		'slug'  => 'learning-resources',
		'image' => '2025/07/LR-logo-tagline.jpg',
		'alt'   => 'LR-logo-tagline',
	),
	array(
		'slug'  => 'polym',
		'image' => '2025/07/logo-polydron.jpg',
		'alt'   => 'logo-polydron',
	),
	array(
		'slug'  => 'science-can',
		'image' => '2025/07/Science-Can.jpg',
		'alt'   => 'Science-Can',
	),
	array(
		'slug'  => 'tacco',
		'image' => 'woocommerce-placeholder.png.webp',
		'alt'   => '',
		'large' => true,
	),
	array(
		'slug'  => 'top-bright',
		'image' => '2025/07/top-bright.jpg',
		'alt'   => 'top-bright',
	),
	array(
		'slug'  => 'viga',
		'image' => '2025/07/viga.jpg',
		'alt'   => 'viga',
	),
	array(
		'slug'  => 'viking-toys',
		'image' => '2025/07/Logo-vikingtoys.jpg',
		'alt'   => 'Logo-vikingtoys',
	),
	array(
		'slug'  => 'zzzmoon',
		'image' => '2025/07/Zzzmoon.jpg',
		'alt'   => 'Zzzmoon',
	),
);
?>
<div class="lfk-brand-directory-page" role="main">
	<div class="lfk-shell">
		<div class="lfk-brand-visual-grid">
			<?php foreach ( $brand_cards as $card ) : ?>
				<?php
				$brand = get_term_by( 'slug', $card['slug'], 'product_brand' );
				if ( ! $brand || is_wp_error( $brand ) ) {
					continue;
				}
				$is_large = ! empty( $card['large'] );
				?>
				<section class="lfk-brand-visual-card<?php echo $is_large ? ' is-large' : ''; ?>">
					<a href="<?php echo esc_url( get_term_link( $brand ) ); ?>">
						<img src="<?php echo esc_url( lfk_remote_upload_url( $card['image'] ) ); ?>" alt="<?php echo esc_attr( $card['alt'] ); ?>" width="<?php echo $is_large ? '415' : '300'; ?>" height="<?php echo $is_large ? '415' : '300'; ?>" loading="lazy">
						<h2><?php echo esc_html( $brand->name ); ?></h2>
					</a>
				</section>
				<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
get_footer();
