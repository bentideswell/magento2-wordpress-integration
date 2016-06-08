<?php
/**
 * Please do not modify this file.
 * Any changes you make will be overwritten
 * This file is not used to display your blog.
 */
 
get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php if ( have_posts() ) : ?>

			<?php if ( is_home() && ! is_front_page() ) : ?>
				<header>
					<h1><?php single_post_title(); ?></h1>
				</header>
			<?php endif; ?>

			<?php while ( have_posts() ) : the_post() ?>
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>				
					<header class="entry-header">
						<?php
							if ( is_single() ) :
								the_title( '<h1 class="entry-title">', '</h1>' );
							else :
								the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), '</a></h2>' );
							endif;
						?>
					</header>				
					<div class="entry-content">
						<?php
							the_content( sprintf(
								__( 'Continue reading %s', 'twentyfifteen' ),
								the_title( '<span class="screen-reader-text">', '</span>', false )
							) );

						?>
					</div><!-- .entry-content -->				
					<?php
						// Author bio.
						if ( is_single() && get_the_author_meta( 'description' ) ) :
							get_template_part( 'author-bio' );
						endif;
					?>
				
					<footer class="entry-footer">

					</footer><!-- .entry-footer -->
				
				</article><!-- #post-## -->
			<?php endwhile;

			// Previous/next page navigation.
			the_posts_pagination( array(
				'prev_text'          => __( 'Previous page', 'twentyfifteen' ),
				'next_text'          => __( 'Next page', 'twentyfifteen' ),
				'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'twentyfifteen' ) . ' </span>',
			) );

		// If no content, include the "No posts found" template.
		else :
			get_template_part( 'content', 'none' );

		endif;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>

