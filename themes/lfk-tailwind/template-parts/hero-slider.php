<?php
$front_id = (int) get_option( 'page_on_front' );
$slides   = function_exists( 'get_field' ) ? get_field( 'hero_banner_slider', $front_id ) : array();

if ( empty( $slides ) || ! is_array( $slides ) ) {
	return;
}
?>
<section class="lfk-hero" data-lfk-hero>
	<div class="lfk-hero-track">
		<?php foreach ( $slides as $index => $slide ) :
			$link        = isset( $slide['set_link']['url'] ) ? $slide['set_link']['url'] : '#';
			$desktop_img = isset( $slide['desktop_image'] ) && is_array( $slide['desktop_image'] ) ? $slide['desktop_image'] : null;
			$mobile_img  = isset( $slide['mobile_image'] ) && is_array( $slide['mobile_image'] ) ? $slide['mobile_image'] : null;
			if ( ! $desktop_img && ! $mobile_img ) {
				continue;
			}
			?>
			<a class="lfk-hero-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" href="<?php echo esc_url( $link ); ?>" data-lfk-slide>
				<?php if ( $desktop_img ) : ?>
					<img class="lfk-hero-desktop<?php echo 0 === $index ? ' skip-lazy' : ''; ?>" src="<?php echo esc_url( $desktop_img['url'] ); ?>" alt="<?php echo esc_attr( $desktop_img['alt'] ); ?>" width="<?php echo esc_attr( $desktop_img['width'] ); ?>" height="<?php echo esc_attr( $desktop_img['height'] ); ?>" <?php echo 0 === $index ? 'fetchpriority="high" data-no-lazy="1"' : 'loading="lazy"'; ?>>
				<?php endif; ?>
				<?php if ( $mobile_img ) : ?>
					<img class="lfk-hero-mobile<?php echo 0 === $index ? ' skip-lazy' : ''; ?>" src="<?php echo esc_url( $mobile_img['url'] ); ?>" alt="<?php echo esc_attr( $mobile_img['alt'] ); ?>" width="<?php echo esc_attr( $mobile_img['width'] ); ?>" height="<?php echo esc_attr( $mobile_img['height'] ); ?>" <?php echo 0 === $index ? 'fetchpriority="high" data-no-lazy="1"' : 'loading="lazy"'; ?>>
				<?php endif; ?>
			</a>
		<?php endforeach; ?>
		<button class="lfk-hero-nav lfk-hero-prev" type="button" aria-label="<?php esc_attr_e( 'Previous slide', 'lfk-tailwind' ); ?>" data-lfk-prev><?php echo lfk_svg_icon( 'chevron-left' ); ?></button>
		<button class="lfk-hero-nav lfk-hero-next" type="button" aria-label="<?php esc_attr_e( 'Next slide', 'lfk-tailwind' ); ?>" data-lfk-next><?php echo lfk_svg_icon( 'chevron-right' ); ?></button>
	</div>
	<div class="lfk-hero-dots" aria-hidden="true">
		<?php foreach ( $slides as $index => $slide ) : ?>
			<button class="lfk-hero-dot <?php echo 0 === $index ? 'is-active' : ''; ?>" type="button" data-lfk-dot></button>
		<?php endforeach; ?>
	</div>
</section>

