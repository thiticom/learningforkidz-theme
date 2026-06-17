<?php
$terms = get_terms( array(
	'taxonomy'   => 'age',
	'hide_empty' => false,
	'orderby'    => 'term_id',
	'order'      => 'ASC',
) );

if ( empty( $terms ) || is_wp_error( $terms ) ) {
	return;
}
?>
<section class="lfk-age-band" aria-label="<?php esc_attr_e( 'Shop by age', 'lfk-tailwind' ); ?>">
	<div class="lfk-age-inner">
		<h2 class="lfk-age-title">Shop By Age</h2>
		<div class="lfk-age-list">
			<?php foreach ( $terms as $term ) : ?>
				<a class="lfk-age-pill" href="<?php echo esc_url( get_term_link( $term ) ); ?>"><?php echo esc_html( $term->name ); ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

