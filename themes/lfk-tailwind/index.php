<?php
get_header();
?>
<main id="primary" class="lfk-shell py-10">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	}
	?>
</main>
<?php
get_footer();

