<?php

if ( ! function_exists( 'lfk_render_how_to_static_content' ) ) {
	function lfk_render_how_to_static_content( $content ) {
		$heading = '';
		if ( preg_match( '/<h2\b[^>]*>.*?<\/h2>/is', $content, $heading_match ) ) {
			$heading = $heading_match[0];
			$content = str_replace( $heading_match[0], '', $content );
		}

		$parts = preg_split( '/(<img\b[^>]*>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
		$steps = array();

		foreach ( $parts as $part ) {
			if ( preg_match( '/^<img\b[^>]*>$/i', trim( $part ) ) ) {
				$steps[] = array(
					'image'   => $part,
					'caption' => '',
				);
				continue;
			}

			$caption = trim( $part );
			if ( '' === $caption || empty( $steps ) ) {
				continue;
			}

			$caption = str_replace( array( '</br>', '<br/>', '<br />' ), '<br>', $caption );
			$caption = preg_replace( '/[ \t\r\n]+/', ' ', $caption );
			$steps[ count( $steps ) - 1 ]['caption'] .= $caption;
		}

		?>
		<div class="lfk-how-to-content">
			<?php echo wp_kses_post( $heading ); ?>
			<div class="lfk-how-to-steps">
				<?php foreach ( $steps as $step ) : ?>
					<div class="lfk-how-to-step">
						<div class="lfk-how-to-caption"><?php echo wp_kses_post( $step['caption'] ); ?></div>
						<div class="lfk-how-to-media"><?php echo wp_kses_post( $step['image'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}

get_header();

while ( have_posts() ) :
	the_post();
	$content = get_the_content( null, false, get_the_ID() );
	$slug    = get_post_field( 'post_name', get_the_ID() );
	?>
	<main id="primary" class="lfk-page lfk-static-page lfk-static-page-<?php echo esc_attr( $slug ); ?>">
		<div class="lfk-shell">
			<article class="lfk-content-page lfk-static-content">
				<div class="lfk-rich-text">
					<?php
					if ( 'how-to-orders' === $slug ) {
						lfk_render_how_to_static_content( $content );
					} else {
						echo wp_kses_post( do_shortcode( shortcode_unautop( $content ) ) );
					}
					?>
				</div>
			</article>
		</div>
	</main>
	<?php
endwhile;

get_footer();
