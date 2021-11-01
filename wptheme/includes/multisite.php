<?php
/**
 *
 */
add_filter('fishpig_api_v1_data', function($data) {
    return array_merge(
        $data,
        [
            'network' => fp_get_network_data(),
        ]
    );
});

/**
 * @return array
 */
function fp_get_network_data()
{
    $data = [
        'enabled' => false,
        'blog_id' => 1
    ];
    
    if (is_multisite()) {
        $data['enabled'] = true;
        $data['blog_id'] = get_current_blog_id();
    }

    return $data;
}
