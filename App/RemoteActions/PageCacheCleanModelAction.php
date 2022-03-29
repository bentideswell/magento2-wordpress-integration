<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\RemoteActions;

use Magento\Framework\Exception\NoSuchEntityException;

class PageCacheCleanModelAction implements RemoteActionInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Cache $cache,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \FishPig\WordPress\Model\PostRepository $postRepository
    ) {
       $this->cache = $cache;
       $this->eventManager = $eventManager;
       $this->postRepository = $postRepository;
    }

    /**
     * @inheritDoc
     */
    public function run(array $args = []): ?array
    {
        if (!isset($args['ids'])) {
            return ['status' => false];
        }

        $countOk = 0;
        $countError = 0;
        foreach ($args['ids'] as $postType => $ids) {
            foreach ($ids as $id) {
                try {
                    $post = $this->postRepository->getWithType($id, $postType);
                    $post->cleanModelCache();
                    $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $post]);
                    ++$countOk;
                } catch (NoSuchEntityException $e) {
                    ++$countError;
                }
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
