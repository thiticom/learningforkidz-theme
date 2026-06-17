<?php

get_header();
?>
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
