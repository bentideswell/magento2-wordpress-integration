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
        add_action(
            'save_post',
            function($postId, $post, $update) {
                if (wp_is_post_revision($postId)) {
                    return;
                }

                if ($update) {
                    \FishPig\WordPress\X\RemoteActions::triggerAction(
                        'pagecache.clean.model',
                        [
                            'ids' => [
                                $post->post_type => [$postId]
                            ]
                        ]
                    );
                } else {
                    $this->flushWordPressCache();
                }
            },
            100,
            3
        );

        add_action(
            'wp_trash_post',
            function ($postId) {
                $this->flushWordPressCache();
            }
        );
    }

    /**
     *
     */
    public function flushWordPressCache(): void
    {
        \FishPig\WordPress\X\RemoteActions::triggerAction(
            'caches.clean.tags',
            [
                'tags' => [
                    'wordpress'
                ]
            ]
        );
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
