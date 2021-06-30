<?php
/**
 * The default template for displaying content
 *
 * Used for both single and index/archive/search.
 */
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
?>
<article id="fp-post-<?php the_ID() ?>"><?php the_content() ?></article>