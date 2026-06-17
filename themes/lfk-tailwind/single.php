<?php

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="primary" class="lfk-post-single">
		<div class="lfk-shell">
			<article class="lfk-post-article">
				<header class="lfk-post-header">
					<h1><?php the_title(); ?></h1>
					<div class="lfk-article-meta">
						<span><?php echo lfk_svg_icon( 'calendar' ); ?><?php echo esc_html( get_the_date() ); ?></span>
						<span><?php echo lfk_svg_icon( 'comment' ); ?><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></span>
					</div>
				</header>
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="lfk-post-featured">
						<?php the_post_thumbnail( 'large' ); ?>
					</div>
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
