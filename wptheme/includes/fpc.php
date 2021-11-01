<?php
/**
 *
 */
add_action('save_post', function($post_id) {
    // If this is just a revision, don't do anything
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    // Make an invalidation call to Magento
    $salt = get_option( 'fishpig_salt' );

    if (!$salt) {
        $salt = wp_generate_password( 64, true, true );
        update_option( 'fishpig_salt', $salt );
    }

    $nonce_tick = ceil(time() / ( 86400 / 2 ));

    $action = 'invalidate_wordpress_post_' . $post_id;

    $nonce = substr( hash_hmac( 'sha256', $nonce_tick . '|fishpig|' . $action, $salt ), -12, 10 );

    $invalidationUrl = add_query_arg([
            '_fp_invalidate' => $nonce,
            '_time' => time(),
        ], 
        get_permalink($post_id)
    );

    // Send the HTTP request
    if (function_exists('curl_init')) {
        $ch = curl_init($invalidationUrl);

        curl_setopt_array($ch, [
            CURLOPT_URL => $invalidationUrl,
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);

        curl_exec($ch);
        curl_close($ch);
    } else {
        wp_remote_get($invalidationUrl);
    }
});
