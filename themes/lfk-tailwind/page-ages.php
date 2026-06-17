<?php

get_header();

$age_cards = array(
	array(
		'slug'  => '18-months',
		'image' => '2022/09/18months.webp',
		'alt'   => 'ของเล่นเด็กอายุ 18 เดือน',
	),
	array(
		'slug'  => '2-years',
		'image' => '2022/09/2years-1.webp',
		'alt'   => 'ของเล่นเด็กอายุ 2 ปี',
	),
	array(
		'slug'  => '3-4-years',
		'image' => '2022/09/5-7years.webp',
		'alt'   => 'ของเล่นเด็กอายุ 3-4 ปี',
	),
	array(
		'slug'  => '5-7-years',
		'image' => '2022/09/3-4years.webp',
		'alt'   => 'ของเล่นเด็กอายุ 5-7 ปี',
	),
	array(
		'slug'  => '8-up',
		'image' => '2022/09/8yearsplus.webp',
		'alt'   => 'ของเล่นเด็กอายุ 8 ปี',
	),
);
?>
<div class="lfk-ages-page" role="main">
	<div class="lfk-shell">
		<div class="lfk-age-visual-grid">
			<?php foreach ( $age_cards as $card ) : ?>
				<?php
				$age = get_term_by( 'slug', $card['slug'], 'age' );
				if ( ! $age || is_wp_error( $age ) ) {
					continue;
				}
				?>
				<section class="lfk-age-visual-card">
					<a href="<?php echo esc_url( get_term_link( $age ) ); ?>">
						<img src="<?php echo esc_url( lfk_remote_upload_url( $card['image'] ) ); ?>" alt="<?php echo esc_attr( $card['alt'] ); ?>" width="350" height="350" loading="lazy">
						<h2><?php echo esc_html( $age->name ); ?></h2>
					</a>
				</section>
				<?php endforeach; ?>
		</div>
	</div>
</div>
<?php
get_footer();
