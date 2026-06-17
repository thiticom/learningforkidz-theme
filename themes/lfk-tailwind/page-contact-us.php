<?php

get_header();
?>
<main id="primary" class="lfk-contact-page">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php esc_html_e( 'ติดต่อเรา', 'lfk-tailwind' ); ?></h1>
		</header>

		<div class="lfk-contact-grid">
			<section class="lfk-contact-info">
				<h2><?php esc_html_e( 'บริษัท พัฒนาวิชาการและประเมินผล จำกัด', 'lfk-tailwind' ); ?></h2>
				<p>เลขที่ 209/33 ซอย งามวงศ์วาน 35 (พงษ์เพชรพัฒนา) แขวง ทุ่งสองห้อง เขตหลักสี่ กรุงเทพมหานคร</p>
				<p><a href="tel:0926085225">092-608-5225</a></p>
				<p><a href="mailto:learningforkidz.th@gmail.com">learningforkidz.th@gmail.com</a></p>
				<div class="lfk-socials lfk-contact-socials">
					<a href="https://www.facebook.com/learningforkidz.th" target="_blank" rel="noopener" aria-label="Facebook"><?php echo lfk_svg_icon( 'facebook' ); ?></a>
					<a href="https://www.instagram.com/learningforkidz.th/" target="_blank" rel="noopener" aria-label="Instagram"><?php echo lfk_svg_icon( 'instagram' ); ?></a>
					<a href="https://lin.ee/lwPOrbnb" target="_blank" rel="noopener" aria-label="Line"><?php echo lfk_svg_icon( 'line' ); ?></a>
				</div>
			</section>
			<section class="lfk-contact-form">
				<?php echo do_shortcode( '[contact-form-7 id="a12c0e5" title="Contact form 1"]' ); ?>
			</section>
		</div>

		<div class="lfk-map">
			<iframe loading="lazy" src="https://maps.google.com/maps?q=No.%20209%2F33%2C%20Soi%20Ngamwongwan%2035%20%28Phongphet%20Phatthana%29%2C%20Thung%20Song%20Hong%20Subdistrict%2C%20Lak%20Si%20District%2C%20Bangkok&amp;t=m&amp;z=10&amp;output=embed&amp;iwloc=near" title="Learning for Kidz map"></iframe>
		</div>
	</div>
</main>
<?php
get_footer();
