<?php

get_header();
?>
<div class="lfk-contact-custom-page" role="main">
	<section class="lfk-contact-stage">
		<h2 class="lfk-contact-page-title"><?php esc_html_e( 'ติดต่อเรา', 'lfk-tailwind' ); ?></h2>

		<div class="lfk-contact-card">
			<section class="lfk-contact-info-panel">
				<h2><?php esc_html_e( 'ติดต่อเรา', 'lfk-tailwind' ); ?></h2>
				<p class="lfk-contact-company">บริษัท พัฒนาวิชาการและประเมินผล จำกัด</p>
				<p>เลขที่ 209/33 ซอย งามวงศ์วาน 35 (พงษ์เพชรพัฒนา) แขวง ทุ่งสองห้อง เขตหลักสี่ กรุงเทพมหานคร</p>
				<p><a href="tel:0926085225">0926085225</a></p>
				<p><a href="mailto:learningforkidz.th@gmail.com">learningforkidz.th@gmail.com</a></p>
				<div class="lfk-socials lfk-contact-panel-socials">
					<a href="https://www.facebook.com/learningforkidz.th" target="_blank" rel="noopener" aria-label="Facebook"><?php echo lfk_svg_icon( 'facebook' ); ?></a>
					<a href="https://www.instagram.com/learningforkidz.th/" target="_blank" rel="noopener" aria-label="Instagram"><?php echo lfk_svg_icon( 'instagram' ); ?></a>
					<a href="https://www.tiktok.com/@learningforkidz" target="_blank" rel="noopener" aria-label="Tiktok"><?php echo lfk_svg_icon( 'tiktok' ); ?></a>
					<a href="https://lin.ee/lwPOrbnb" target="_blank" rel="noopener" aria-label="Line"><?php echo lfk_svg_icon( 'line' ); ?></a>
					<a href="https://www.youtube.com/@learningforkidz999" target="_blank" rel="noopener" aria-label="Youtube"><?php echo lfk_svg_icon( 'youtube' ); ?></a>
				</div>
			</section>
			<section class="lfk-contact-form-panel">
				<?php echo do_shortcode( '[contact-form-7 id="a12c0e5" title="Contact form 1"]' ); ?>
				<div class="lfk-contact-map">
					<iframe loading="lazy" src="https://maps.google.com/maps?q=No.%20209%2F33%2C%20Soi%20Ngamwongwan%2035%20%28Phongphet%20Phatthana%29%2C%20Thung%20Song%20Hong%20Subdistrict%2C%20Lak%20Si%20District%2C%20Bangkok&amp;t=m&amp;z=10&amp;output=embed&amp;iwloc=near" title="Learning for Kidz map"></iframe>
				</div>
			</section>
		</div>
	</section>
</div>
<?php
get_footer();
