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
    <?= apply_filters('fishpig_index_template_after_loop_html', '') ?>
<?php get_footer() ?>