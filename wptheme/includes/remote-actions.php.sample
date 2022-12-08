<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
namespace FishPig\WordPress\X;

class RemoteActions
{
    /**
     *
     */
    static public function triggerAction(string $name, array $args = [])
    {
        $permalinkStructure = get_option('permalink_structure');

        if ($permalinkStructure && substr($permalinkStructure, -1) === '/') {
            $homeUrl = trailingslashit(get_home_url());
        } else {
            $homeUrl = untrailingslashit(get_home_url());
        }

        $actionUrl = add_query_arg(
            [
                'fishpig-wp' => array_merge(
                    [
                        'action' => $name,
                        'key' => \FishPig\WordPress\X\AuthorisationKey::getKey(),
                        'time' => time(),
                    ],
                    $args
                )
            ],
            $homeUrl
        );

        // Send the HTTP request
        if (function_exists('curl_init')) {
            $ch = curl_init($actionUrl);

            curl_setopt_array($ch, [
                CURLOPT_URL => $actionUrl,
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_USERAGENT => 'FishPig/M2WP'
            ]);

            curl_exec($ch);
            curl_close($ch);
        } else {
            wp_remote_get($actionUrl);
        }
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
