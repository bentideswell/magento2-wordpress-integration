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
		<article id="fp-post-<?php the_ID() ?>"><?php the_content() ?></article>
	<?php endwhile ?>
<?php endif; ?>
<?php get_footer() ?>