<?php

get_header();

$age_order = array( '18-months', '2-years', '3-4-years', '5-7-years', '8-up' );
$ages      = get_terms( array(
	'taxonomy'   => 'age',
	'hide_empty' => false,
) );

if ( $ages && ! is_wp_error( $ages ) ) {
	usort( $ages, function ( $a, $b ) use ( $age_order ) {
		$a_index = array_search( $a->slug, $age_order, true );
		$b_index = array_search( $b->slug, $age_order, true );
		$a_index = false === $a_index ? 99 : $a_index;
		$b_index = false === $b_index ? 99 : $b_index;
		return $a_index <=> $b_index;
	} );
}
?>
<main id="primary" class="lfk-directory-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php esc_html_e( 'Age', 'lfk-tailwind' ); ?></h1>
		</header>

		<?php if ( $ages && ! is_wp_error( $ages ) ) : ?>
			<div class="lfk-term-grid lfk-age-term-grid">
				<?php foreach ( $ages as $age ) : ?>
					<a class="lfk-term-card lfk-age-term-card" href="<?php echo esc_url( get_term_link( $age ) ); ?>">
						<span class="lfk-age-badge"><?php echo esc_html( $age->name ); ?></span>
						<span class="lfk-term-count"><?php echo esc_html( sprintf( _n( '%d product', '%d products', (int) $age->count, 'lfk-tailwind' ), (int) $age->count ) ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
