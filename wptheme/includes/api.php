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
                    $data = apply_filters('fishpig_api_v1_data', [
                        '_time' => time()
                    ]);
                    
                    foreach ($data as $key => $value) {
                        if (!is_array($value) && !is_string($value) && is_callable($value)) {
                            $data[$key] = $value();
                        }
                    }
                    
                    return $data;
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
        
        
        // Allow CORS
        remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
        
        add_filter(
            'rest_pre_serve_request',
            function($value) {
        		header('Access-Control-Allow-Origin: ' .get_home_url());
                header('Access-Control-Allow-Methods: GET' );
                header('Access-Control-Allow-Credentials: true' );
                header('Access-Control-Expose-Headers: Link', false );

                return $value;
            }
        );
    }
);
