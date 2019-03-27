<?php
/**
 * The Template for displaying all single posts
 */

get_header(); ?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
			<?php while (have_posts()) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
				<?php previous_post_link( '%link', '%title' ); ?>
				<?php next_post_link( '%link', '%title ' ); ?>
				<?php comments_template( '', true ); ?>
			<?php endwhile; ?>
		</div>
	</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>