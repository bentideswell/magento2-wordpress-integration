<?php
/**
 *
 */
fishpig_psw_handle_no_amd_js();

if (isset($_GET['post_type']) && $_GET['post_type'] === 'elementor_library') {
    define('WP_HOME', get_site_url());
}

/**
 *
 */
add_filter(
    'fishpig_api_data_taxonomy_ignore_list',
    function($taxonomies) {
        return array_merge(
            $taxonomies,
            [
                'elementor_library_type',
                'elementor_library_category',
                'tribe_events_cat',
                'elementor_font_type',
            ]
        );
    }
);

/**
 *
 */
add_filter(
    'fishpig_api_data_post_type_ignore_list',
    function($postTypes) {
        return array_merge(
            $postTypes,
            [
                'tribe_events', 
                'elementor_library',
                'e-landing-page'
            ]
        );
    }
);

/**
 *
 */
function fishpig_psw_handle_no_amd_js()
{
    $requestUri = !empty($_SERVER['REQUEST_URI']) ? trim($_SERVER['REQUEST_URI']) : '';
    $noAmdPrefix = '/fishpig/js/';

    if (($pos = strpos($requestUri, $noAmdPrefix)) !== false) {
        $relativeJsSourceFile = substr($requestUri, $pos + strlen($noAmdPrefix));
    
        if (($pos = strpos($relativeJsSourceFile, '?')) !== false) {
            $relativeJsSourceFile = substr($relativeJsSourceFile, 0, $pos);
        }
        
        if (substr($relativeJsSourceFile, -3) !== '.js') {
            return '';
        }
        
        $jsSourceFile = realpath(ABSPATH . $relativeJsSourceFile);
        
        if (!$jsSourceFile || strpos($jsSourceFile, ABSPATH) !== 0) {
            return '';
        }
        
        $data = file_get_contents($jsSourceFile);
        $data = str_replace('define.amd',     'define.zyx', $data);
        $data = str_replace('typeof exports', 'typeof exportssdfsdfsdf', $data);

        $jsTargetFile = ABSPATH . ltrim($noAmdPrefix, '/') . $relativeJsSourceFile;
        $jsTargetPath = dirname($jsTargetFile);
        
        if (!is_dir($jsTargetPath)) {
            mkdir($jsTargetPath, 0755, true);
        }
        
        if (is_dir($jsTargetPath)) {
            file_put_contents($jsTargetFile, $data);
        }

        header('Content-Type: application/javascript');
        echo $data;
        exit;
    }
}

/**
 * Change the Element preview link to a local preview link
 * This allows the Elementor editor to work without all of the CORS issues
 */
add_filter('elementor/editor/localize_settings', function($config) {
    $previewUrl = get_site_url() . '/index.php/';
    $previewUrl .= ltrim(
        str_replace(
            get_home_url(),
            '',
            $config['initial_document']['urls']['preview']
        ),
        '/'
    );
    
    $config['initial_document']['urls']['preview'] = $previewUrl;
   
    return $config;
});

/**
 * Visual Composer
 */
add_filter('vcv:frontend:pageEditable:url', function($postLink) {
    return fishpig_make_preview_url($postLink);
});