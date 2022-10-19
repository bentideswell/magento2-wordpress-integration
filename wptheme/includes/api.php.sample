<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
namespace FishPig\WordPress\X;

use WP_REST_Request;

class Api
{
    /**
     *
     */
    public function __construct()
    {
        // Setup direct rest URL
        add_filter(
            'rest_url',
            function($url, $path){
                return get_site_url() . '/index.php?rest_route=' . $path;

                $find   = '/wp-json/';
                $pos    = strpos($rest, $find);
                $extra  = '';

                if ($pos !== false && strlen($rest) > $pos+strlen($find)) {
                    $extra = substr($rest, $pos+strlen($find));
                }

                return get_option('siteurl') . '/index.php?rest_route=/' . ltrim($extra, '/');
            },
            100,
            2
        );

        // Setup REST API endpoints
        add_action(
            'rest_api_init',
            function() {
                register_rest_route(
                    'fishpig/v1',
                    '/hello/',
                    [
                        'methods' => 'GET',
                        'callback' => function(\WP_REST_Request $request) {
                            return ['status' => 1];
                        },
                        'permission_callback' => '__return_true'
                    ]
                );

                register_rest_route(
                    'fishpig/v1',
                    '/data/',
                    [
                        'methods' => 'GET',
                        'callback' => function(\WP_REST_Request $request) {
                            $data = apply_filters('fishpig_api_v1_data', [
                                'key' => $request->get_header(\FishPig\WordPress\X\AuthorisationKey::KEY_HEADER_NAME),
                                'time' => time()
                            ]);

                            foreach ($data as $key => $value) {
                                if (!is_array($value) && !is_string($value) && is_callable($value)) {
                                    $data[$key] = $value();
                                }
                            }

                            return $data;
                        },
                        'permission_callback' => '\FishPig\WordPress\X\AuthorisationKey::isRestRequestAuthorised'
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
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
