<?php
/**
 *
 */
add_action('init', function() {
    add_rewrite_rule('^wordpress/post/preview/?$', 'index.php', 'top');
    
    // We have Yoast so lets disable some redirects
    if (isset($GLOBALS['wpseo_rewrite'])) {
        remove_filter('request', array($GLOBALS['wpseo_rewrite'], 'request'));
    }
});

/**
 *
 */
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    set_post_thumbnail_size(9999, 9999);

    add_theme_support('post-formats', array('aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat'));

    if (function_exists('show_admin_bar')) {
        show_admin_bar(false);
    }

    /* Remove wptexturize to fix shortcodes */
    remove_filter('the_content', 'wptexturize');
});

/**
 *
 */
add_filter('gutenberg_use_widgets_block_editor', '__return_false' );
add_filter('use_widgets_block_editor',           '__return_false' );
add_filter('wp_fatal_error_handler_enabled',     '__return_false' );
add_filter('wp_calculate_image_srcset',          '__return_false');