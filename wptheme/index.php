<?php
/**
 * Please do not modify this file.
 * Any changes you make will be overwritten
 * This file is not used to display your blog.
 */
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
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>				
				<header class="entry-header">
					<?php if (is_single()): ?>
						<h1 class="entry-title"><?php the_title() ?></h1>
					<?php else: ?>
						<h2 class="entry-title"><a href="<?php echo esc_url(get_permalink()) ?>"><?php the_title() ?></a></h2>
					<?php endif; ?>
				</header>				
				<div class="entry-content"><?php the_content() ?></div>
			</article>
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