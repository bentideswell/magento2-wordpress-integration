<?php
/**
 *
 */
add_action('after_setup_theme', function() {
    remove_filter('template_redirect', 'redirect_canonical');
    remove_action('template_redirect', 'wp_old_slug_redirect');
});

/**
 *
 */
add_filter('redirect_canonical', function($redirect_url){
    return is_404() ? false : $redirect_url;
});