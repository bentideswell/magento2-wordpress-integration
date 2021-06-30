<?php
/**
 * The Template for displaying all single posts
 */
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
?>
<?php get_header() ?>
<?php while (have_posts()): the_post(); ?>
	<?php get_template_part('content', get_post_format()) ?>
<?php endwhile; ?>
<?php get_sidebar() ?>
<?php get_footer() ?>