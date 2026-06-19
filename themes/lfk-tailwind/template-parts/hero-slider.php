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
			$desktop_srcset = $desktop_id ? lfk_version_local_upload_srcset( wp_get_attachment_image_srcset( $desktop_id, 'full' ) ) : '';
			$mobile_srcset  = $mobile_id ? lfk_version_local_upload_srcset( wp_get_attachment_image_srcset( $mobile_id, 'full' ) ) : '';
			$desktop_url    = $desktop_img ? lfk_version_local_upload_url( $desktop_img['url'] ) : '';
			$mobile_url     = $mobile_img ? lfk_version_local_upload_url( $mobile_img['url'] ) : '';
			$is_first       = $is_real && 0 === $index;
			$priority_attrs = $is_first ? 'loading="eager" fetchpriority="high" data-no-lazy="1"' : 'loading="lazy"';
			$placeholder    = 'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%221%22 height=%221%22 viewBox=%220 0 1 1%22%3E%3C/svg%3E';
			$image_src      = $is_first ? esc_url( $desktop_url ?: $mobile_url ) : esc_attr( $placeholder );
			?>
			<a class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" href="<?php echo esc_url( $link ); ?>" style="--lfk-slide-offset: <?php echo esc_attr( $offset ); ?>;" <?php echo $is_real ? 'data-lfk-slide' : ''; ?>>
				<picture class="lfk-hero-picture">
					<?php if ( $mobile_img ) : ?>
						<source media="(max-width: 767px)" <?php echo $is_first ? 'srcset' : 'data-srcset'; ?>="<?php echo esc_attr( $mobile_srcset ?: $mobile_url ); ?>" sizes="100vw">
					<?php endif; ?>
					<img class="lfk-hero-image<?php echo $is_first ? ' skip-lazy' : ''; ?>" src="<?php echo $image_src; ?>" <?php if ( $desktop_srcset ) : ?><?php echo $is_first ? 'srcset' : 'data-srcset'; ?>="<?php echo esc_attr( $desktop_srcset ); ?>" sizes="100vw"<?php endif; ?><?php if ( ! $is_first ) : ?> data-src="<?php echo esc_url( $desktop_url ?: $mobile_url ); ?>"<?php endif; ?> alt="<?php echo esc_attr( $desktop_img['alt'] ?? $mobile_img['alt'] ?? '' ); ?>" width="<?php echo esc_attr( $desktop_img['width'] ?? $mobile_img['width'] ?? 1920 ); ?>" height="<?php echo esc_attr( $desktop_img['height'] ?? $mobile_img['height'] ?? 495 ); ?>" <?php echo $priority_attrs; ?>>
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
