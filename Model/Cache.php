<?php
/**
 *
 *
 *
 */
namespace FishPig\WordPress\Model;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\App\Cache\Type\FrontendPool;

class Cache extends TagScope
{
    /**
     * Cache type code unique among all cache types
     *
     * @const string
     */
    const TYPE_IDENTIFIER = 'fishpig_wordpress';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     *
     * @const string
     */
    const CACHE_TAG = 'FISHPIG_WP';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
