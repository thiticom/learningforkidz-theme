<?php

get_header();
?>
<main id="primary" class="lfk-post-index">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php the_archive_title(); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<div class="lfk-archive-description"><?php the_archive_description(); ?></div>
			<?php endif; ?>
		</header>

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
</main>
<?php
get_footer();
