<?php
/**
 *
 */
add_filter('fishpig_api_v1_data', function($data) {
    return array_merge(
        $data,
        [
            'post_types' => fp_get_post_type_data(),
            'taxonomies' => fp_get_taxonomy_data(),
        ]
    );
});

/**
 * @return array
 */
function fp_get_post_type_data()
{
    $postTypesToIgnore = array(
        'attachment', 
        'nav_menu_item', 
        'revision',
        'tribe_events', 
        'wp_block', 
        'user_request', 
        'oembed_cache', 
        'customize_changeset', 
        'custom_css',
        'elementor_library',
        'e-landing-page',
    );

    // Include plugin.php so we can check whether WPPermastructure is active
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $wpPermastructureActive = is_plugin_active('wp-permastructure/wp-permastructure.php');
    $cptPermalinksActive    = is_plugin_active('custom-post-type-permalinks/custom-post-type-permalinks.php');

    if (!($postTypeData = get_post_types(array('_builtin' => false, 'public' => true), 'objects'))) {
        return [];
    }

    foreach ($postTypeData as $type => $data) {
        if ((int)$data->public !== 1 || empty($data->rewrite) || in_array($type, $postTypesToIgnore)) {
            unset($postTypeData[$type]);
            continue;
        }

        // Convert object to array and then filter keys
        $data = array_filter(
            array_intersect_key(
                json_decode(json_encode($data), true), 
                array_flip(
                    [
                        'name',
                        'labels',
                        'public',
                        'hierarchical',
                        'exclude_from_search',
                        'taxonomies',
                        'has_archive',
                        '_builtin',
                        'rewrite',
                    ]
                )
            )
        );

        if (!empty($data['labels'])) {
            $data['labels'] = array_intersect_key($data['labels'],  array_flip(['name', 'singular_name']));
        }
        
        if (!empty($data['rewrite'])) {
            $data['rewrite'] = array_intersect_key($data['rewrite'], array_flip(['slug', 'with_front']));
            $data['rewrite']['slug_original'] = isset($data['rewrite']['slug']) ? $data['rewrite']['slug'] : '';
        }

        if ($type === 'post') {
            $data['rewrite'] = ['slug' => get_option('permalink_structure')];
            $data['taxonomies'] = array_merge(isset($data['taxonomies']) ? $data['taxonomies'] : [], ['category', 'post_tag']);
        } elseif ($type === 'page') {
            $data['rewrite'] = ['slug' => '%postname%/'];
        } elseif ($wpPermastructureActive && ($wpPermastructure = get_option($type . '_permalink_structure'))) {
            $data['rewrite']['slug'] = $wpPermastructure;
        } elseif ($cptPermalinksActive && ($wpStructure = get_option($type . '_structure'))) {
            if ($data['rewrite']['with_front']) {
                $data['rewrite']['slug'] = '/' . $data['rewrite']['slug'] . $wpStructure;
            } else {
                $data['rewrite']['slug'] = $wpStructure;
            }
        }

        $postTypeData[$type] = $data;
    }

    return $postTypeData;
}

/**
 * @return array
 */
function fp_get_taxonomy_data()
{
    if (!($taxonomyData = get_taxonomies(array('_builtin' => false), 'objects'))) {
        return [];
    }

    $taxonomiesToIgnore = ['nav_menu', 'link_category', 'post_format'];

    foreach ($taxonomyData as $taxonomy => $data) {
        if (in_array($taxonomy, $taxonomiesToIgnore)) {
            unset($taxonomyData[$taxonomy]);
            continue;
        }

        // Convert object to array and then filter keys
        $data = array_filter(
            array_intersect_key(
                json_decode(json_encode($data), true), 
                array_flip(
                    [
                        'name',
                        'labels',
                        'public',
                        'hierarchical',
                        '_builtin',
                        'rewrite',
                    ]
                )
            )
        );

        if (!empty($data['labels'])) {
            $data['labels']  = array_intersect_key($data['labels'],  array_flip(['name', 'singular_name']));
        }
        
        if (!empty($data['rewrite'])) {
            $data['rewrite'] = array_intersect_key(
                $data['rewrite'], 
                array_flip(['slug', 'with_front', 'hierarchical'])
            );
        }
        
        $data['taxonomy'] = $taxonomy;

        $taxonomyData[$taxonomy] = $data;
    }

    return $taxonomyData;
}
