<?php
/**
 * @package FishPig_WordPress_Root
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/root/
 */
declare(strict_types=1);

namespace FishPig\WordPress\X;

class Fpc
{
    /**
     * @const string
     */
    const NONCE_OPTION_NAME_PREFIX = '_fishpig_nonce_post_';
    const NONCE_ACTION_PREFIX = 'invalidate_wordpress_post_';
    const INVALIDATION_URL_NONCE_FIELD = 'wp_fpc_nonce';
    
    /**
     *
     */
    public function __construct()
    {
        add_action('save_post', function($postId) {
            if (wp_is_post_revision($postId)) {
                return;
            }

            $optionName = self::NONCE_OPTION_NAME_PREFIX . $postId;
            $nonce = md5(
                implode(
                    '::',
                    [
                        \FishPig\WordPress\X\AuthorisationKey::getKey(),
                        $postId,
                        date('Y/m/d H'),
                        floor(date('i') / 5)
                    ]
                )
            );
            
            update_option($optionName, $nonce);
        
            $invalidationUrl = add_query_arg([
                    self::INVALIDATION_URL_NONCE_FIELD => $nonce,
                    '_time' => time(),
                ], 
                get_permalink($postId)
            );

            // Send the HTTP request
            if (function_exists('curl_init')) {
                $ch = curl_init($invalidationUrl);
        
                curl_setopt_array($ch, [
                    CURLOPT_URL => $invalidationUrl,
                    CURLOPT_HEADER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
                    CURLOPT_CONNECTTIMEOUT => 10
                ]);
        
                curl_exec($ch);
                curl_close($ch);
                
                delete_option($optionName);
            } else {
                wp_remote_get($invalidationUrl);
            }
        });
    }
}
