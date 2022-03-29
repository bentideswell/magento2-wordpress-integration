<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\RemoteActions;

interface RemoteActionInterface
{
    /**
     * @param  array $args = []
     * @return ?array
     */
    public function run(array $args = []): ?array;
}
