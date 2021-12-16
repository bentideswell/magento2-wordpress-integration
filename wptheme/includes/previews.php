<?php
/**
 *
 */
namespace FishPig\WordPress\X;

class Previews
{
    public function __construct()
    {
        add_action('init', function() {
            // Create redirect for preview URLS
            if (isset($_GET['preview']) && !empty($_SERVER['REQUEST_URI'])) {
                if (get_post($_GET['preview_id'])->post_status !== 'publish') {
                    if (preg_match('/^.*(\/index.php\/)(.*)(\?.*)$/', $_SERVER['REQUEST_URI'], $m)) {
                        if ($m[2]) {
                            wp_redirect(get_site_url() . $m[1] . $m[3], 302, 'FishPig Preview');
                            exit; // phpcs:ignore
                        }
                    }
                }
            }
        });

        add_filter('get_post_status', function($post_status, $post) {            
            if (false === \FishPig\WordPress\X\AuthorisationKey::isAuthorised()) {
                return $post_status;
            }
            
            if (is_preview() && $post_status === 'draft') {
                return 'publish';
            }
            
            return $post_status;
        }, 100, 2);

        if (isset($_GET['preview_nonce']) && isset($_GET['preview_id'])) {
            $_GET['preview_nonce'] = wp_create_nonce('post_preview_' . (int)$_GET['preview_id']);
        }
    }
}
