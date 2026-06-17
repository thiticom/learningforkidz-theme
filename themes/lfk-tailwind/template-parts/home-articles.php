<?php
$articles = new WP_Query( array(
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 4,
	'orderby'             => 'date',
	'order'               => 'DESC',
	'ignore_sticky_posts' => true,
) );

if ( ! $articles->have_posts() ) {
	return;
}
?>
<section class="lfk-articles-section">
	<div class="lfk-shell">
		<?php lfk_section_heading( 'บทความ' ); ?>
		<div class="lfk-article-grid">
			<?php
			while ( $articles->have_posts() ) :
				$articles->the_post();
				?>
				<article class="lfk-article-card">
					<a class="lfk-article-image" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
						<?php endif; ?>
					</a>
					<div class="lfk-article-body">
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<div class="lfk-article-meta">
							<span><?php echo lfk_svg_icon( 'calendar' ); ?><?php echo esc_html( get_the_date() ); ?></span>
							<span><?php echo lfk_svg_icon( 'comment' ); ?><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></span>
						</div>
					</div>
				</article>
				<?php
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
