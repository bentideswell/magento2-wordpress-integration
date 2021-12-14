<?php
/**
 *
 */
namespace FishPig\WordPress\X;

use WP_REST_Request;

class Api
{    
    /**
     * @const string
     */
    const AUTH_TOKEN_HEADER_NAME = 'X-FishPig-Auth';
    const AUTH_TOKEN_OPTION_NAME = 'fishpig_auth_token';
    const AUTH_TOKEN_OPTION_NAME_PREVIOUS = 'fishpig_auth_token_previous';
    
    /**
     * @var bool
     */
    static private $isAuthTokenValid = null;
   
    /**
     *
     */
    public function __construct()
    {
        // Setup direct rest URL
        add_filter(
            'rest_url', 
            function($rest){
                $find   = '/wp-json/';
                $pos    = strpos($rest, $find);
                $extra  = '';
    
                if ($pos !== false && strlen($rest) > $pos+strlen($find)) {
                    $extra = substr($rest, $pos+strlen($find));
                }
    
                return get_option('siteurl') . '/index.php?rest_route=/' . ltrim($extra, '/');
            }
        );
        
        // Setup REST API endpoints
        add_action(
            'rest_api_init',
            function() {
                register_rest_route(
                    'fishpig/v1', 
                    '/data/', 
                    [
                        'methods' => 'GET',
                        'callback' => function(\WP_REST_Request $request) {
                            $data = apply_filters('fishpig_api_v1_data', [
                                'key' => $request->get_header(self::AUTH_TOKEN_HEADER_NAME),
                                'time' => time()
                            ]);
                            
                            foreach ($data as $key => $value) {
                                if (!is_array($value) && !is_string($value) && is_callable($value)) {
                                    $data[$key] = $value();
                                }
                            }
                            
                            return $data;
                        },
                        'permission_callback' => '\FishPig\WordPress\X\Api::isAuthTokenValid',
                    ]
                );

                // Add CORS header for Magento URL
                add_filter(
                    'rest_dispatch_request',
                    function($result)
                    {
                        global $fpCorsHeaderFlag;
                        
                        if (!isset($fpCorsHeaderFlag) || $fpCorsHeaderFlag !== true) {
                            $fpCorsHeaderFlag = true;
                            $integratedMagentoUrl = get_home_url();
                            
                            if (($pos = strpos($integratedMagentoUrl, '/', strlen('https://'))) !== false) {
                                $integratedMagentoUrl = substr($integratedMagentoUrl, 0, $pos);
                            }
                        
                            if (get_http_origin() === $integratedMagentoUrl) {
                            	header('Access-Control-Allow-Origin: ' . get_http_origin());
                            }
                        }
                    
                        return $result;
                    }
                );
            }
        );

    } 
   
    /**
     * @return bool
     */
    static public function isAuthTokenValid(\WP_REST_Request $request = null): bool
    {
        if (self::$isAuthTokenValid === null) {
            self::$isAuthTokenValid = false;

            if ($request !== null) {
                $token = $request->get_header(self::AUTH_TOKEN_HEADER_NAME);
            } else {
                $serverHeaderKey = 'HTTP_' . str_replace('-', '_', strtoupper(self::AUTH_TOKEN_HEADER_NAME));

                if (!empty($_SERVER[$serverHeaderKey])) {
                    $token = $_SERVER[$serverHeaderKey];
                }
            }

            if (!empty($token)) {    
                foreach ([self::AUTH_TOKEN_OPTION_NAME, self::AUTH_TOKEN_OPTION_NAME_PREVIOUS] as $key) {
                    if (get_option($key) === $token) {
                        self::$isAuthTokenValid = true;
                        break;
                    }
                }
            }
        }
        
        return self::$isAuthTokenValid;
    }
}
