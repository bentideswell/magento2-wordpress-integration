<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Network implements \FishPig\WordPress\Model\NetworkInterface
{
    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return false;
    }

    /**
     * @return int
     */
    public function getBlogId(): int
    {
        return \FishPig\WordPress\Model\NetworkInterface::DEFAULT_BLOG_ID;
    }

    /**
     * @return int
     */
    public function getSiteId(): int
    {
        return \FishPig\WordPress\Model\NetworkInterface::DEFAULT_SITE_ID;
    }

    /**
     * return array
     */
    public function getNetworkObjects(): array
    {
        return [];
    }
}
