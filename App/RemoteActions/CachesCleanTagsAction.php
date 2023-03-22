<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\RemoteActions;

class CachesCleanTagsAction implements RemoteActionInterface
{
    /**
     * @auto
     */
    protected $cachePool = null;

    /**
     * @auto
     */
    protected $cacheState = null;

    /**
     * @auto
     */
    protected $cacheTypes = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cachePool,
        \Magento\Framework\App\Cache\State $cacheState,
        array $cacheTypes = []
    ) {

        $this->cacheTypes = array_unique(array_merge(
            [
                'full_page',
                'bolt_fpc',
                'block_html'
            ],
            $cacheTypes
        ));

       $this->cachePool = $cachePool;
       $this->cacheState = $cacheState;
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): ?array
    {
        if (!empty($args['tags'])) {
            $tags = array_unique($args['tags']);
            $mode = \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG;
        } else {
            $tags = [];
            $mode = \Zend_Cache::CLEANING_MODE_ALL;
        }

        $countError = 0;
        $countOk = 0;

        foreach ($this->cacheTypes as $typeId) {
            if ($this->cacheState->isEnabled($typeId)) {
                if ($cache = $this->cachePool->get($typeId)) {
                   $cache->clean($mode, $tags);
                   $countOk++;
                } else {
                    $countError++;
                }
            } else {
                $countError++;
            }
        }


        return [
            'status' => true,
            'count' => [
                'total' => $countOk + $countError,
                'ok' => $countOk,
                'error' => $countError
            ]
        ];
    }
}
