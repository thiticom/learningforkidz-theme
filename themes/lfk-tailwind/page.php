<?php

get_header();

while ( have_posts() ) :
	the_post();
	$content = get_the_content();
	?>
	<main id="primary" class="lfk-page">
		<div class="lfk-shell">
			<article class="lfk-content-page">
				<?php if ( false === stripos( $content, '<h1' ) ) : ?>
					<h1 class="lfk-page-title"><?php the_title(); ?></h1>
				<?php endif; ?>
				<div class="lfk-rich-text">
					<?php the_content(); ?>
				</div>
			</article>
		</div>
	</main>
	<?php
endwhile;

get_footer();
