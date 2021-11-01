<?php
/**
 *
 */
add_filter('rest_url', function($rest){
    $find   = '/wp-json/';
    $pos    = strpos($rest, $find);
    $extra  = '';

    if ($pos !== false && strlen($rest) > $pos+strlen($find)) {
        $extra = substr($rest, $pos+strlen($find));
    }

    return get_option('siteurl') . '/index.php?rest_route=/' . ltrim($extra, '/');
});

/**
 *
 */
add_action(
    'rest_api_init',
    function() {
        register_rest_route(
            'fishpig/v1', 
            '/data/', 
            [
                'methods' => 'GET',
                'callback' => function() {
                    return apply_filters('fishpig_api_v1_data', [
                        '_time' => time()
                    ]);
                },
            ]
        );
        
        // Version
        register_rest_route(
            'fishpig/v1', 
            '/theme-hash/', 
            [
                'methods' => 'GET',
                'callback' => function() {
                    return ['hash' => FISHPIG_THEME_HASH];
                },
            ]
        );
    }
);
