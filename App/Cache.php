<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Cache extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * @auto
     */
    protected $cacheFrontendPool = null;

    /**
     * @const string
     */
    const TYPE_IDENTIFIER = 'fishpig_wordpress';

    /**
     * @const string
     */
    const CACHE_TAG = 'FISHPIG_WP';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        \Magento\Framework\App\Cache\State $cacheState,
        $tag = self::CACHE_TAG
    ) {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            $tag
        );

        try {
            if (!$cacheState->isEnabled(self::TYPE_IDENTIFIER)) {
                $cacheState->setEnabled(self::TYPE_IDENTIFIER, true);
                $cacheState->persist();
            }
        } catch (\Throwable $e) {
            // Ignore
        }
    }

    /**
     * @param  mixed $data
     * @param  string $identifier
     * @param  array $tags
     * @param  int $lifeTime = null
     * @return
     */
    public function saveApiData($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->save(
            $data,
            $identifier,
            array_merge($tags, ['fishpig-wordpress-api']),
            $lifeTime ?? 60 * 60 * 4
        );
    }
}
