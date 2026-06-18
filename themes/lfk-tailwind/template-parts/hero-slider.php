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
			$desktop_id = ! empty( $desktop_img['ID'] ) ? (int) $desktop_img['ID'] : ( ! empty( $desktop_img['id'] ) ? (int) $desktop_img['id'] : 0 );
			$mobile_id  = ! empty( $mobile_img['ID'] ) ? (int) $mobile_img['ID'] : ( ! empty( $mobile_img['id'] ) ? (int) $mobile_img['id'] : 0 );
			$desktop_srcset = $desktop_id ? wp_get_attachment_image_srcset( $desktop_id, 'full' ) : '';
			$mobile_srcset  = $mobile_id ? wp_get_attachment_image_srcset( $mobile_id, 'full' ) : '';
			$priority_attrs = $is_real && 0 === $index ? 'fetchpriority="high" data-no-lazy="1"' : 'loading="lazy"';
			?>
			<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_url( $link ); ?>" style="--lfk-slide-offset: <?php echo esc_attr( $offset ); ?>;" <?php echo $is_real ? 'data-lfk-slide' : ''; ?>>
				<picture class="lfk-hero-picture">
					<?php if ( $mobile_img ) : ?>
						<source media="(max-width: 767px)" srcset="<?php echo esc_attr( $mobile_srcset ?: $mobile_img['url'] ); ?>" sizes="100vw">
					<?php endif; ?>
					<img class="lfk-hero-image<?php echo $is_real && 0 === $index ? ' skip-lazy' : ''; ?>" src="<?php echo esc_url( $desktop_img ? $desktop_img['url'] : $mobile_img['url'] ); ?>" <?php if ( $desktop_srcset ) : ?>srcset="<?php echo esc_attr( $desktop_srcset ); ?>" sizes="100vw"<?php endif; ?> alt="<?php echo esc_attr( $desktop_img['alt'] ?? $mobile_img['alt'] ?? '' ); ?>" width="<?php echo esc_attr( $desktop_img['width'] ?? $mobile_img['width'] ?? 1920 ); ?>" height="<?php echo esc_attr( $desktop_img['height'] ?? $mobile_img['height'] ?? 495 ); ?>" <?php echo $priority_attrs; ?>>
				</picture>
			</a>
			<?php
		};

		foreach ( $slides as $index => $slide ) {
			$render_slide( $slide, $index, 0, true );
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
