<?php
/**
 *
 *
 *
 */
namespace FishPig\WordPress\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use FishPig\WordPress\Model\Factory;
use FishPig\WordPress\Model\OptionManager;
use Magento\Framework\App\CacheInterface;

class Invalidate extends Action
{
    /**
     *
     * @var Factory
     *
     */
    protected $factory;

    /**
     *
     * @var OptionManager
     *
     */
    protected $optionManager;

    /**
     *
     * @var CacheInterface
     *
     */
    protected $cacheManager;

    /**
     *
     * @var ManagerInterface
     *
     */
    protected $eventManager;

    /**
     *
     *
     *
     */
    public function __construct(Context $context,OptionManager $optionManager, Factory $factory, CacheInterface $cacheManager)
    {
        $this->optionManager = $optionManager;
        $this->factory       = $factory;
        $this->cacheManager  = $cacheManager;
        $this->eventManager  = $context->getEventManager();

        parent::__construct($context);
    }

    /**
     *
     *
     * @return void
     */
    public function execute()
    {
        $this->getResponse()->appendBody(
            json_encode([
                'result' => $this->invalidateCache() ? 'success' : 'failure'
            ])
        );
    }

    /**
     *
     * Attempt to invalidate cache entry
     *
     */
    protected function invalidateCache()
    {
        $postId = (int)$this->getRequest()->getParam('id');
        $nonce  = $this->getRequest()->getParam('nonce');

        if (!$this->verifyNonce($nonce, 'invalidate_' . $postId)) {
            return false;
        }

        $post = $this->factory->create('Post')->load($postId);

        if (!$post->getId()) {
            return false;
        }

        // Clean cache related objects and then allow FPC plugins to do the same
        $post->cleanModelCache();
        $this->eventManager->dispatch('clean_cache_by_tags', ['object' => $post]);

        return true;
    }

    /**
     *
     * Validate given nonce
     *
     */
    protected function verifyNonce($nonce, $action)
    {
        if (!($salt = $this->optionManager->getOption('fishpig_salt'))) {
            return false;
        }

        $nonce_tick = ceil(time() / ( 86400 / 2 ));

        // 0-12 hours
        if (substr(hash_hmac('sha256', $nonce_tick . '|fishpig|' . $action, $salt), -12, 10) == $nonce) {
            return true;
        }

        // 12-24 hours
        if (substr(hash_hmac('sha256', ($nonce_tick - 1) . '|fishpig|' . $action, $salt), -12, 10) == $nonce) {
            return true;
        }

        return false;
    }
}
