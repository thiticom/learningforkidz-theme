<?php

get_header();

$posts_page_id = (int) get_option( 'page_for_posts' );
$title         = $posts_page_id ? get_the_title( $posts_page_id ) : __( 'บทความ', 'lfk-tailwind' );
?>
<main id="primary" class="lfk-post-index">
	<div class="lfk-shell">
		<header class="lfk-post-archive-header">
			<h1><?php echo esc_html( $title ); ?></h1>
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
