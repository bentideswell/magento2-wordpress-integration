<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\External;

class PathResolver
{
    /**
     * @param  int $storeId
     * @return ?string
     */
    public function getPath(int $storeId): ?string
    {
        return null;
    }
}
