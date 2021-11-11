<?php
/**
 *
 */
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
