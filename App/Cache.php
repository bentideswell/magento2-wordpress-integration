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
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
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
