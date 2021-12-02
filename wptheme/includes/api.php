<?php
/**
 *
 */
define('FISHPIG_API_AUTH_TOKEN_HEADER_NAME', 'X-FishPig-Auth');
define('FISHPIG_API_AUTH_TOKEN_OPTION_NAME', 'fishpig_auth_token');
define('FISHPIG_API_AUTH_TOKEN_PREVIOUS_OPTION_NAME', 'fishpig_auth_token_previous');

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
 * Check auth token
 */
function fishpig_api_auth_check(\WP_REST_Request $request) {
    if (!($token = $request->get_header(FISHPIG_API_AUTH_TOKEN_HEADER_NAME))) {
        return false;
    }
    
    foreach ([FISHPIG_API_AUTH_TOKEN_OPTION_NAME, FISHPIG_API_AUTH_TOKEN_PREVIOUS_OPTION_NAME] as $key) {
        if (get_option($key) === $token) {
            return true;
        }
    }
    
    return false;
}

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
                'callback' => function(WP_REST_Request $request) {
                    
                    
                    $data = apply_filters('fishpig_api_v1_data', [
                        'key' => $request->get_header(FISHPIG_API_AUTH_TOKEN_HEADER_NAME),
                        'time' => time()
                    ]);
                    
                    foreach ($data as $key => $value) {
                        if (!is_array($value) && !is_string($value) && is_callable($value)) {
                            $data[$key] = $value();
                        }
                    }
                    
                    return $data;
                },
                'permission_callback' => 'fishpig_api_auth_check',
            ]
        );

        // Allow CORS
        remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
        
        add_filter(
            'rest_pre_serve_request',
            function($value) {
        		header('Access-Control-Allow-Origin: ' . get_home_url());
                header('Access-Control-Allow-Methods: GET' );
                header('Access-Control-Allow-Credentials: true' );
                header('Access-Control-Expose-Headers: Link', false );

                return $value;
            }
        );
    }
);
