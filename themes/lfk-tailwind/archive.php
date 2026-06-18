<?php

get_header();
?>
<section class="lfk-article-archive-hero" aria-label="<?php esc_attr_e( 'Article', 'lfk-tailwind' ); ?>">
	<div class="lfk-article-archive-hero-bg"></div>
	<div class="lfk-article-archive-title"><?php esc_html_e( 'Article', 'lfk-tailwind' ); ?></div>
</section>
<div class="lfk-post-index" role="main">
	<div class="lfk-shell">
		<?php if ( have_posts() ) : ?>
			<div class="lfk-article-grid lfk-post-grid">
				<?php
				$card_index = 0;
				while ( have_posts() ) :
					the_post();
					lfk_article_card( get_post(), $card_index );
					$card_index++;
				endwhile;
				?>
			</div>
			<div class="lfk-pagination">
				<?php the_posts_pagination(); ?>
			</div>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
