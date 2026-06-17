<?php
$kanoodle_items = array(
	array( 'href' => '/product/kanoodle/', 'src' => '2025/06/Kanoodle.jpg', 'alt' => 'Kanoodle' ),
	array( 'href' => '/product/kanoodle-genius/', 'src' => '2025/06/Kanoodle-genius-game.jpg', 'alt' => 'Kanoodle Genius' ),
	array( 'href' => '/product/kanoodle-extreme/', 'src' => '2025/06/Kanoodle-extreme.jpg', 'alt' => 'Kanoodle Extreme' ),
	array( 'href' => '/product/kanoodle-head-to-head/', 'src' => '2025/06/Kanoodle-head-to-head.jpg', 'alt' => 'Kanoodle Head-to-Head' ),
);
?>
<section class="lfk-media-section">
	<div class="lfk-shell">
		<a class="lfk-video-card" href="<?php echo esc_url( lfk_remote_upload_url( '2025/07/video_568257661322133805-TOpDragd.mp4' ) ); ?>" target="_blank" rel="noopener">
			<img src="<?php echo esc_url( lfk_remote_upload_url( '2025/07/S__20471815_0.jpg' ) ); ?>" alt="Learning for Kidz video" width="1170" height="641" loading="lazy">
			<span class="lfk-video-play"><?php echo lfk_svg_icon( 'play' ); ?></span>
		</a>
		<div class="lfk-code-grid">
			<img src="<?php echo esc_url( lfk_remote_upload_url( '2025/07/left_code_img.jpeg' ) ); ?>" alt="Learning for Kidz channel" width="600" height="300" loading="lazy">
			<img src="<?php echo esc_url( lfk_remote_upload_url( '2025/07/right_code_img.jpeg' ) ); ?>" alt="Learning for Kidz channel" width="600" height="300" loading="lazy">
		</div>
	</div>
</section>

<section class="lfk-kanoodle-section">
	<div class="lfk-shell">
		<?php lfk_section_heading( 'ของเล่นฝึกสมอง: ไวรัลบน TikTok' ); ?>
		<div class="lfk-kanoodle-grid">
			<?php foreach ( $kanoodle_items as $item ) : ?>
				<a class="lfk-kanoodle-card" href="<?php echo esc_url( home_url( $item['href'] ) ); ?>">
					<img src="<?php echo esc_url( lfk_remote_upload_url( $item['src'] ) ); ?>" alt="<?php echo esc_attr( $item['alt'] ); ?>" width="300" height="300" loading="lazy">
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
