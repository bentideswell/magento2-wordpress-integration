<?php
/**
 * @package FishPig_WordPress_Root
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/root/
 */
declare(strict_types=1);

namespace FishPig\WordPress\X;

class Fpc
{
    /**
     *
     */
    public function __construct()
    {
        add_action('save_post', function($postId) {
            if (wp_is_post_revision($postId)) {
                return;
            }

            $post = get_post($postId);
            $posType = $post->post_type;

            \FishPig\WordPress\X\RemoteActions::triggerAction(
                'pagecache.clean.model',
                [
                    'ids' => [
                        $posType => [$postId]
                    ]
                ]
            );
        });
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
