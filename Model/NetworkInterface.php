<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

interface NetworkInterface
{
    /**
     * @const int
     */
    const DEFAULT_BLOG_ID = 1;
    
    /**
     * @const int
     */
    const DEFAULT_SITE_ID = 1;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @return int
     */
    public function getBlogId(): int;
    
    /**
     * @return int
     */
    public function getSiteId(): int;
    
    /**
     * return array
     */
    public function getNetworkObjects(): array;
    
    /**
     * @param  string $key
     * @return mixed;
     */
    //public function getBlogTableValue(string $key);

    /**
     * @return false
     */
    //public function getSitePath();
}
