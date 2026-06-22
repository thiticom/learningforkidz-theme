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
$mobile_posts = $article_posts;
$mobile_featured_post = null;

foreach ( $article_posts as $article_post ) {
	if ( false !== stripos( get_the_title( $article_post ), 'kanoodle' ) ) {
		$mobile_featured_post = $article_post;
		break;
	}
}

if ( $mobile_featured_post ) {
	$mobile_posts = array_values( array_filter( $article_posts, function ( $article_post ) use ( $mobile_featured_post ) {
		return $article_post->ID !== $mobile_featured_post->ID;
	} ) );
	array_unshift( $mobile_posts, $mobile_featured_post );
}

$mobile_dot_count = count( $mobile_posts );
$mobile_posts = array_merge( $mobile_posts, $mobile_posts );
?>
<section class="lfk-articles-section">
	<div class="lfk-shell">
		<?php lfk_section_heading( 'บทความ' ); ?>
		<div class="lfk-carousel lfk-article-carousel lfk-article-carousel--desktop" data-lfk-carousel data-lfk-visible-desktop="4" data-lfk-visible-tablet="2" data-lfk-visible-mobile="1">
			<div class="lfk-carousel-viewport">
				<div class="lfk-home-article-track lfk-home-article-track--desktop" data-lfk-carousel-track>
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
			</div>
		</div>
		<div class="lfk-carousel lfk-article-carousel lfk-article-carousel--mobile" data-lfk-carousel data-lfk-visible-desktop="1" data-lfk-visible-tablet="1" data-lfk-visible-mobile="1">
			<div class="lfk-carousel-viewport">
				<div class="lfk-home-article-track lfk-home-article-track--mobile" data-lfk-carousel-track>
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
			</div>
			<?php if ( $mobile_dot_count > 1 ) : ?>
				<div class="lfk-carousel-dots lfk-article-dots" aria-label="<?php esc_attr_e( 'Article carousel pagination', 'lfk-tailwind' ); ?>">
					<?php for ( $dot_index = 0; $dot_index < $mobile_dot_count; $dot_index++ ) : ?>
						<button class="lfk-carousel-dot<?php echo 0 === $dot_index ? ' is-active' : ''; ?>" type="button" aria-label="<?php echo esc_attr( sprintf( __( 'Show article slide %d', 'lfk-tailwind' ), $dot_index + 1 ) ); ?>" data-lfk-carousel-dot></button>
					<?php endfor; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php wp_reset_postdata(); ?>
	</div>
</section>
