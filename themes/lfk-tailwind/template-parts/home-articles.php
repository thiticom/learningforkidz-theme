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

$article_posts = $articles->posts;
$desktop_posts = array_merge( $article_posts, $article_posts, $article_posts );
$mobile_posts = array(
	$article_posts[3] ?? null,
	$article_posts[0] ?? null,
	$article_posts[1] ?? null,
	$article_posts[2] ?? null,
	$article_posts[3] ?? null,
	$article_posts[0] ?? null,
);
?>
<section class="lfk-articles-section">
	<div class="lfk-shell">
		<?php lfk_section_heading( 'บทความ' ); ?>
		<div class="lfk-home-article-track lfk-home-article-track--desktop">
			<?php foreach ( $desktop_posts as $post ) : ?>
				<?php setup_postdata( $post ); ?>
				<article class="lfk-article-card">
					<a class="lfk-article-image" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
						<?php endif; ?>
					</a>
					<div class="lfk-article-body">
						<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
						<div class="lfk-article-meta">
							<span><?php echo lfk_svg_icon( 'calendar' ); ?><?php echo esc_html( get_the_date() ); ?></span>
							<span><?php echo lfk_svg_icon( 'comment' ); ?><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></span>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="lfk-home-article-track lfk-home-article-track--mobile">
			<?php foreach ( array_filter( $mobile_posts ) as $post ) : ?>
				<?php setup_postdata( $post ); ?>
				<article class="lfk-article-card">
					<a class="lfk-article-image" href="<?php the_permalink(); ?>">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy' ) ); ?>
						<?php endif; ?>
					</a>
					<div class="lfk-article-body">
						<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
						<div class="lfk-article-meta">
							<span><?php echo lfk_svg_icon( 'calendar' ); ?><?php echo esc_html( get_the_date() ); ?></span>
							<span><?php echo lfk_svg_icon( 'comment' ); ?><?php comments_number( 'No Comments', '1 Comment', '% Comments' ); ?></span>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<?php wp_reset_postdata(); ?>
	</div>
</section>
