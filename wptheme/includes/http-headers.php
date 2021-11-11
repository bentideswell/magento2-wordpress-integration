<?php
/**
 *
 */
add_filter(
    'status_header',
    function($status_header, $code, $description, $protocol){
        if ((int)$code === 404) {
            return '';
        }
    
        return $status_header;
    },
    10,
    4
);

/**
 *
 */
add_filter(
    'wp_headers',
    function($headers){
        if (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'text/html') !== false) {
            unset($headers['Content-Type']);
        }

        return $headers;
    },
    10, 
    4
);
