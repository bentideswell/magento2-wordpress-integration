<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\RemoteActions;

class CacheCleanAction implements RemoteActionInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Cache $cache
    ) {
       $this->cache = $cache; 
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): ?array
    {
        if (!empty($args['ids'])) {
            foreach ($args['ids'] as $cacheId) {
                $this->cache->remove($cacheId);
            }
        } elseif (!empty($args['tags'])) {
            $this->cache->clean(
                \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG,
                $args['tags']
            );
        } elseif (!empty($args['flush'])) {
            $this->cache->flush();
        } else {
            return null;
        }
        
        return ['status' => true];
    }
}
