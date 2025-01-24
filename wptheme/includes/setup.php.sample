<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
namespace FishPig\WordPress\X;

class Setup
{
    /**
     *
     */
    public function __construct()
    {
        add_action('after_setup_theme', function() {
            // Setup theme supports
            add_theme_support('title-tag');
            add_theme_support('post-thumbnails');
            set_post_thumbnail_size(9999, 9999);
            add_theme_support(
                'post-formats',
                ['aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat']
            );

            // Cleanup
            remove_action('wp_head', 'print_emoji_detection_script', 7 );
            remove_action('admin_print_scripts', 'print_emoji_detection_script' );
            remove_action('wp_print_styles', 'print_emoji_styles' );
            remove_action('admin_print_styles', 'print_emoji_styles' );
            remove_action('wp_head', 'wlwmanifest_link');
            remove_action('wp_head', 'wp_generator');
            remove_action('wp_head', 'wp_resource_hints', 2 );
            remove_action('wp_head', 'rsd_link');
            remove_action('wp_head', 'rest_output_link_wp_head', 10);

            add_filter('gutenberg_use_widgets_block_editor', '__return_false');
            add_filter('use_widgets_block_editor',           '__return_false');
            add_filter('wp_fatal_error_handler_enabled',     '__return_false');
            add_filter('wp_calculate_image_srcset',          '__return_false');

            // Stop .htaccess being edited
            add_filter('flush_rewrite_rules_hard','__return_false');

            if (function_exists('show_admin_bar')) {
                show_admin_bar(false);
            }

            /* Remove wptexturize to fix shortcodes */
            remove_filter('the_content', 'wptexturize');

            /**
             * Stop spaces in URLs being converted by WP into dashes.
             * This causes an issue in Magento where /hello world/ loads
             * /hello-world/, which is wrong as this is not the right URL.
             */
            remove_filter('sanitize_title', 'sanitize_title_with_dashes', 10, 3);
            add_filter(
                'sanitize_title',
                function ($title, $raw_title = '', $context = 'display') {
                    return $context === 'query'
                        ? $title
                        : sanitize_title_with_dashes($title, $raw_title, $context);
                },
                10,
                3
            );
        });

        // Setup templates from Magento
        add_action('wp_loaded', function() {
            if ($post_types = get_post_types(['public' => true, '_builtin' => false])) {
                foreach ($post_types as $post_type) {
                    add_filter(
                        "theme_{$post_type}_templates",
                        function($page_templates, $wp_theme, $post) {
                            return [
                                'template-empty' => 'Empty',
                                'template-1column' => '1 Column',
                                'template-2columns-left' => '2 Columns Left',
                                'template-2columns-right' => '2 Columns Right',
                                'template-3columns' => '3 Columns',
                                'template-full-width' => 'Full Width',
                            ] + $page_templates;
                        },
                        10,
                        4
                    );
                }
            }
        });

        add_action('wp_footer',function(){
            wp_deregister_script('wp-embed');

            // Divi
            if (isset($_GET['et_fb'])) {
                wp_dequeue_style('wp-auth-check');
                wp_dequeue_script('wp-auth-check');
                remove_action('wp_print_footer_scripts', 'et_fb_output_wp_auth_check_html', 5);
            }
        }, 12);

        add_action('widgets_init', function() {
            register_sidebar([
                'name' => __( 'Main Sidebar', 'fishpig' ),
                'id' => 'sidebar-main',
                'description' => 'Add widgets here to appear in your left Magento sidebar.',
                'before_widget' => '<aside id="%1$s" class="widget %2$s">',
                'after_widget' => '</aside>',
                'before_title' => '<h2 class="widget-title">',
                'after_title' => '</h2>',
            ]);

            global $wp_widget_factory;

            if (isset($wp_widget_factory->widgets['WP_Widget_Recent_Comments'])) {
                remove_action(
                    'wp_head',
                    [
                        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
                        'recent_comments_style'
                    ]
                );
            }
        });

        // Redirection Protection
        add_filter(
            'wp_redirect',
            function($url) {
                // Allow redirects to siteurl
                if (strpos($url, get_site_url() . '/index.php') === 0) {
                    return $url;
                } else if (strpos($url, get_site_url() . '/wp-') === 0) {
                    return $url;
                }

                // Don't allow redirects to home URL because this can fire off many API calls and get us in a loop
                if (strpos($url, rtrim(get_home_url(), '/')) === 0) {
                    if (\FishPig\WordPress\X\AuthorisationKey::isAuthorised()) {
                        // This looks like an API URL so don't allow the redirect
                        return false;
                    }
                }

                return $url;
            }
        );

        // Stop WPSEO (Yoast) redirecting when index.php is present as this is probably a Magento request
        // This could be further improved to disable redirects when Magento token present
        // But this would make debugging in a browser difficult
        if (strpos($_SERVER['REQUEST_URI'], '/index.php') !== false) {
            if (isset($GLOBALS['wpseo_rewrite'])) {
                remove_filter('request', array($GLOBALS['wpseo_rewrite'], 'request'));
            }
        }

        // Add useful helper to show FishPig index.php in custom templates
        add_filter('fishpig_index_template', function() {
            include_once __DIR__ . '/../index.php';
        });
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
