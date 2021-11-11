<?php
/**
 *
 */
add_action('init', function() {
    foreach (['page_link', 'post_link', ''] as $hook) {
        add_filter($hook, function($url) {
            return fishpig_make_preview_url($url);
        });
    };
});

/**
 *
 */
function fishpig_make_preview_url($url) {
    return ($pos = strpos($url, '?') !== false) ? get_home_url() . '/wordpress/post/preview/' . substr($url, $pos) : $url;
}
