<?php

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="primary" class="lfk-post-single">
		<div class="lfk-shell">
			<article class="lfk-post-article">
				<header class="lfk-post-header">
					<h1><span><?php the_title(); ?></span></h1>
				</header>
				<div class="lfk-rich-text">
					<?php echo lfk_content_with_image_aspect_ratios( apply_filters( 'the_content', get_the_content() ) ); ?>
				</div>
			</article>

			<section class="lfk-post-author-box">
				<?php echo get_avatar( (int) get_the_author_meta( 'ID' ), 100, '', get_the_author(), array( 'class' => 'lfk-post-author-avatar' ) ); ?>
				<div class="lfk-post-author-copy">
					<h4><?php echo esc_html( get_the_author() ); ?></h4>
				</div>
			</section>

			<section class="lfk-post-comments">
				<?php
				comment_form( array(
					'title_reply'          => __( 'ใส่ความเห็น', 'lfk-tailwind' ),
					'title_reply_before'   => '<h2 id="reply-title" class="comment-reply-title">',
					'title_reply_after'    => '</h2>',
					'label_submit'         => __( 'แสดงความเห็น', 'lfk-tailwind' ),
					'comment_notes_before' => '',
					'comment_notes_after'  => '',
					'fields'               => array(
						'author'  => '<p class="comment-form-author"><input id="author" name="author" type="text" value="" size="30"></p>',
						'email'   => '<p class="comment-form-email"><input id="email" name="email" type="email" value="" size="30"></p>',
						'url'     => '<p class="comment-form-url"><input id="url" name="url" type="url" value="" size="30"></p>',
						'cookies' => '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"></p>',
					),
					'comment_field'        => '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8"></textarea></p>',
				) );
				?>
				</section>
			</div>
		</main>

		<?php
		$related_posts = new WP_Query( array(
			'post__not_in'        => array( get_the_ID() ),
			'posts_per_page'      => 3,
			'ignore_sticky_posts' => true,
		) );
		?>
		<?php if ( $related_posts->have_posts() ) : ?>
			<section class="lfk-post-related">
				<h2><?php esc_html_e( 'กระทู้ที่เกี่ยวข้อง', 'lfk-tailwind' ); ?></h2>
				<div class="lfk-article-grid">
					<?php
					$related_index = 0;
					while ( $related_posts->have_posts() ) :
						$related_posts->the_post();
						lfk_article_card( get_post(), $related_index );
						$related_index++;
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</section>
		<?php endif; ?>
		<?php
	endwhile;

get_footer();
