<?php
/**
 * Please do not modify this file.
 * Any changes you make will be overwritten
 * This file is not used to display your blog.
 */
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
?>
<?php get_header() ?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
	<?php if (have_posts()): ?>
		<?php if (is_home() && ! is_front_page()): ?>
			<header>
				<h1><?php single_post_title(); ?></h1>
			</header>
		<?php endif; ?>
		<?php while (have_posts()): the_post() ?>
			<article id="post-<?php the_ID() ?>" <?php post_class(); ?>><?php the_content() ?></article>
		<?php endwhile ?>
		<?php
			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __('Previous'),
				'next_text'          => __('Next'),
				'before_page_number' => __('Page'),
			));
		?>
	<?php else: ?>
		<?php echo get_template_part('content', 'none') ?>
	<?php endif; ?>
	</main>
</div>
<?php get_footer(); ?>