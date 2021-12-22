<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post;

class Invalidate extends \Magento\Framework\App\Action\Action
{
    /**
     * @const string
     */
    const NONCE_OPTION_NAME_PREFIX = '_fishpig_nonce_post_';
    const INVALIDATION_URL_NONCE_FIELD = 'wp_fpc_nonce';

    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->optionRepository = $optionRepository;
        $this->postRepository = $postRepository;
        $this->cacheManager = $cacheManager;
        $this->eventManager = $context->getEventManager();
        $this->serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $this->invalidateCache();
            $response = ['result' => 'success'];
        } catch (\Exception $e) {
            $this->logger->error($e);
            $response = [
                'result' => 'failure',
                'error' => $e->getMessage()
            ];
        }
        
        return $this->getResponse()->setHeader(
            'Content-Type',
            'text/json; charset=utf-8'
        )->setBody(
            $this->serializer->serialize($response)
        );
    }

    /**
     * @return bool
     */
    private function invalidateCache(): bool
    {
        $postId = (int)$this->getRequest()->getParam('id');
        $nonce  = $this->getRequest()->getParam(self::INVALIDATION_URL_NONCE_FIELD);

        if (!$this->isValidNonce($nonce, self::NONCE_OPTION_NAME_PREFIX . $postId)) {
            throw new \FishPig\WordPress\App\Exception('Invalid nonce');
        }

        $post = $this->postRepository->get($postId);

        $post->cleanModelCache();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $post]);

        return true;
    }

    /**
     * @return bool
     */
    protected function isValidNonce($nonce, $action): bool
    {
        return $nonce && $nonce === $this->optionRepository->get($action);
    }
}
