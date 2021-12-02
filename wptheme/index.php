<?php
/**
 * Please do not modify this file.
 * Any changes you make will be overwritten
 * This file is not used to display your blog.
 */
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
?>
<?php get_header() ?>
<?php if (have_posts()): ?>
	<?php while (have_posts()): the_post() ?>
		<fishpig:post:<?php the_ID() ?>><?php the_content() ?></fishpig:post:<?php the_ID() ?>>
	<?php endwhile ?>
<?php endif; ?>
    <?php function_exists('fishpig_psw_render_queue') ? fishpig_psw_render_queue() : null ?>
<?php get_footer() ?>