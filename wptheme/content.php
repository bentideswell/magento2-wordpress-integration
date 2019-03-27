<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php if (!post_password_required() && !is_attachment()): ?>
			<?php the_post_thumbnail() ?>
		<?php endif; ?>
		<?php if ( is_single() ) : ?>
			<h1 class="entry-title"><?php the_title(); ?></h1>
		<?php else : ?>
			<h1 class="entry-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
		<?php endif; ?>
	</header>
	<?php if (is_search()): ?>
		<div class="entry-summary"><?php the_excerpt(); ?></div>
	<?php else : ?>
		<div class="entry-content">
			<?php the_content(__('Continue reading')); ?>
		</div>
	<?php endif; ?>
</article>