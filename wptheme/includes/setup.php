<?php
/**
 *
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
            // Ensures Theme hash is set correctly
            if (get_option(FISHPIG_THEME_OPTION_NAME) !== FISHPIG_THEME_HASH) {
                update_option(FISHPIG_THEME_OPTION_NAME, FISHPIG_THEME_HASH);
            }
            
            // Setup theme supports
            add_theme_support('title-tag');
            add_theme_support('post-thumbnails');
            set_post_thumbnail_size(9999, 9999);
            add_theme_support(
                'post-formats', 
                ['aside', 'image', 'video', 'quote', 'link', 'gallery', 'status', 'audio', 'chat']
            );
        
            // Redirects
            remove_filter('template_redirect', 'redirect_canonical');
            remove_action('template_redirect', 'wp_old_slug_redirect');
            add_filter('redirect_canonical', '__return_false');

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

            if (function_exists('show_admin_bar')) {
                show_admin_bar(false);
            }
        
            /* Remove wptexturize to fix shortcodes */
            remove_filter('the_content', 'wptexturize');
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
        
            remove_action('wp_head', [$wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style']);
        });
    }    
}
