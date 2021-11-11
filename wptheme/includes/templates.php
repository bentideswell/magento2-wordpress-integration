<?php
/**
 *
 */
add_action('wp_loaded', function() {
    if ($post_types = get_post_types(['public' => true, '_builtin' => false])) {
        foreach ($post_types as $post_type) {
            add_filter(
                "theme_{$post_type}_templates", 
                function($page_templates, $wp_theme, $post) {
                    return [
                        'template-empty' => 'Empty',
                        'template-1column' => '1 Column',
                        'template-2columns-left' => '2 Columns Left',
                        'template-2columns-right' => '2 Columns Right',
                        'template-3columns' => '3 Columns',        
                        'template-full-width' => 'Full Width',
                    ] + $page_templates;
                }, 
                10, 
                4
            );
        }
    }
});
