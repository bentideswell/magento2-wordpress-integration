<?php
/**
 *
 */
add_action('after_setup_theme', function() {
    /* Remove the Emoji JS */
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 ); 
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' ); 
    remove_action( 'wp_print_styles', 'print_emoji_styles' ); 
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
});

/**
 *
 */
add_action('wp_footer',function(){
    wp_deregister_script('wp-embed');

    // Divi
    if (isset($_GET['et_fb'])) {
        wp_dequeue_style('wp-auth-check');
        wp_dequeue_script('wp-auth-check');
        remove_action('wp_print_footer_scripts', 'et_fb_output_wp_auth_check_html', 5);
    }
}, 12);
