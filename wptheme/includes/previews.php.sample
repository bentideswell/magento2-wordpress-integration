<?php
/**
 *
 */
namespace FishPig\WordPress\X;

/**
 * This class handles displaying of usually protected content but only when the Authorisation Key is present
 */
class Previews
{
    /**
     *
     */
    public function __construct()
    {
        add_action(
            'init', 
            function() {
                // Create redirect for preview URLS
                if (isset($_GET['preview']) && !empty($_SERVER['REQUEST_URI'])) {
                    $previewId = $_GET['preview_id'] ?? ($_GET['p'] ?? null);

                    if ($previewId && get_post($previewId)->post_status !== 'publish') {
                        if (preg_match('/^.*(\/index.php\/)(.*)(\?.*)$/', $_SERVER['REQUEST_URI'], $m)) {
                            if ($m[2]) {
                                wp_redirect(get_site_url() . $m[1] . $m[3], 302, 'FishPig Preview');
                                exit; // phpcs:ignore
                            }
                        }
                    }
                }
            }
        );

        // This adds a query string parameter to ensure each preview isn't cached
        // As the URL will be different on each preview
        add_filter(
            'preview_post_link',
            function ($link) {
                return add_query_arg('preview_time', time(), $link);
            }
        );
        
        if (\FishPig\WordPress\X\AuthorisationKey::isAuthorised()) {
            // All code here is only executed for AJAX requests from Magento
            // And will have the secret key present as a HTTP header

            // Force draft posts to appear as published
            add_filter(
                'get_post_status', 
                function($post_status, $post) {            
                    if (is_preview() && $post_status === 'draft') {
                        return 'publish';
                    }

                    return $post_status;
                },
                100,
                2
            );

            if (!empty($_GET['preview_id'])) {
                $_GET['preview_nonce'] = wp_create_nonce('post_preview_' . (int)$_GET['preview_id']);
            }

            // Display password protected post content
            // Password protection is implemented in Magento
            add_filter(
                'post_password_required',
                function($flag, $post) {
                    return false;
                },
                100,
                2
            );
        }
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento