<?php
$front_id = (int) get_option( 'page_on_front' );
$slides   = function_exists( 'get_field' ) ? get_field( 'hero_banner_slider', $front_id ) : array();

if ( empty( $slides ) || ! is_array( $slides ) ) {
	return;
}
?>
<section class="lfk-hero" data-lfk-hero>
	<div class="lfk-hero-track">
		<?php
		$render_slide = static function( $slide, $index, $offset, $is_real = false ) {
			$link        = isset( $slide['set_link']['url'] ) ? $slide['set_link']['url'] : '#';
			$desktop_img = isset( $slide['desktop_image'] ) && is_array( $slide['desktop_image'] ) ? $slide['desktop_image'] : null;
			$mobile_img  = isset( $slide['mobile_image'] ) && is_array( $slide['mobile_image'] ) ? $slide['mobile_image'] : null;
			if ( ! $desktop_img && ! $mobile_img ) {
				return;
			}
			$classes = array( 'lfk-hero-slide' );
			if ( $is_real && 0 === $index ) {
				$classes[] = 'is-active';
			}
			if ( -1 === $offset ) {
				$classes[] = 'is-lfk-near-active';
			}
			?>
			<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_url( $link ); ?>" style="--lfk-slide-offset: <?php echo esc_attr( $offset ); ?>;" <?php echo $is_real ? 'data-lfk-slide' : ''; ?>>
				<?php if ( $desktop_img ) : ?>
					<img class="lfk-hero-desktop<?php echo $is_real && 0 === $index ? ' skip-lazy' : ''; ?>" src="<?php echo esc_url( $desktop_img['url'] ); ?>" alt="<?php echo esc_attr( $desktop_img['alt'] ); ?>" width="<?php echo esc_attr( $desktop_img['width'] ); ?>" height="<?php echo esc_attr( $desktop_img['height'] ); ?>" <?php echo $is_real && 0 === $index ? 'fetchpriority="high" data-no-lazy="1"' : 'loading="lazy"'; ?>>
				<?php endif; ?>
				<?php if ( $mobile_img ) : ?>
					<img class="lfk-hero-mobile<?php echo $is_real && 0 === $index ? ' skip-lazy' : ''; ?>" src="<?php echo esc_url( $mobile_img['url'] ); ?>" alt="<?php echo esc_attr( $mobile_img['alt'] ); ?>" width="<?php echo esc_attr( $mobile_img['width'] ); ?>" height="<?php echo esc_attr( $mobile_img['height'] ); ?>" <?php echo $is_real && 0 === $index ? 'fetchpriority="high" data-no-lazy="1"' : 'loading="lazy"'; ?>>
				<?php endif; ?>
			</a>
			<?php
		};

		if ( is_front_page() && count( $slides ) >= 7 ) {
			$clone_order = array( 2, 3, 4, 5, 6, 0, 1, 2, 3, 4, 5, 6, 0, 1, 2 );
			foreach ( $clone_order as $clone_index => $slide_index ) {
				$render_slide( $slides[ $slide_index ], $slide_index, $clone_index - 5, 5 === $clone_index );
			}
		} else {
			foreach ( $slides as $index => $slide ) {
				$render_slide( $slide, $index, $index, true );
			}
		}
		?>
		<button class="lfk-hero-nav lfk-hero-prev" type="button" aria-label="‹" data-lfk-prev>‹</button>
		<button class="lfk-hero-nav lfk-hero-next" type="button" aria-label="›" data-lfk-next>›</button>
	</div>
	<div class="lfk-hero-dots" aria-hidden="true">
		<?php foreach ( $slides as $index => $slide ) : ?>
			<button class="lfk-hero-dot <?php echo 0 === $index ? 'is-active' : ''; ?>" type="button" data-lfk-dot></button>
		<?php endforeach; ?>
	</div>
</section>
