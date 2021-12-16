<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
namespace FishPig\WordPress\X;

class Misc
{
    /**
     *
     */
    public function __construct()
    {
        add_action('save_post', function($post_id) {
            try {
                $post = get_post($post_id);
                $content = apply_filters('the_content', $post->post_content);
        
                if (!empty($GLOBALS['wp_embed'])) {
                    $content = $GLOBALS['wp_embed']->autoembed($content);
                }
        
                // Auto include the related products shortcode
                if (class_exists('FishPig_RelatedProducts')) {
                    if ((int)get_option('fprp_autoinclude', 1) === 1) {
                        $content .= '[related_products]';
                    }
                }
        
                update_post_meta($post_id, '_post_content_rendered', $content);
            } catch (\Exception $e) {
                // Do nothing
            }
        });
        
        /**
         *
         */
        add_action('admin_menu',function() {
            global $submenu;
        
            if (isset($submenu['themes.php'])) {
                foreach ($submenu['themes.php'] as $it => $menuItem ) {
                    if (in_array('Customize', $menuItem) || in_array('Customizer', $menuItem)) {
                        unset($submenu['themes.php'][$it]);
                    }
                }
            }
        });
        
        /**
         *
         */
        add_filter('post_type_link', function($postLink, $post){
            if (strpos($postLink, '%') === false) {
                return $postLink;
            }
        
            if (!preg_match_all('/\/%([^%]+)%\//U', $postLink, $matches)) {
                return $postLink;
            }
        
            foreach ($matches[1] as $it => $taxonomy) {
                $token = $matches[0][$it];
                $change = '/';
        
                if ($terms = get_the_terms($post->ID, $taxonomy)) {
                    foreach ($terms as $term) {
                        if (is_object($term)) {
                            $change = '/' . $term->slug . '/';
                            break;
                        }
                    }
                }
        
                $postLink = str_replace($token, $change, $postLink);
            }
        
            return $postLink;
        }, 10, 4);
        
        /**
         *
         */
        add_filter('comment_post_redirect', function($location) {
            if (($hashPosition = strpos($location, '#')) !== false) {
                $hash = substr($location, $hashPosition+1);
                $location = substr($location, 0, $hashPosition);
        
                $location = add_query_arg(
                    [
                        '_hash' => $hash
                    ],
                    $location
                );
            }
        
            return $location;
        });
    }
}
