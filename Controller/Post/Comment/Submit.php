<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post\Comment;

class Submit extends \Magento\Framework\App\Action\Action
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Model\PostFactory $postFactory,
        \FishPig\WordPress\Model\UrlInterface $wpUrlBuilder
    ) {
        $this->postFactory = $postFactory;
        $this->wpUrlBuilder = $wpUrlBuilder;

        parent::__construct($context);
    }
    
    /**
     *
     */
    public function execute()
    {
        $redirect = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT
        );

        try {
            if (($postId = (int)$this->getRequest()->getParam('post_id')) === 0) {
                throw new \FishPig\WordPress\App\Exception('Invalid post ID.');
            }
            
            $post = $this->postFactory->create()->load($postId);
            
            if (!$post->getId()) {
                throw new \FishPig\WordPress\App\Exception('Invalid post loaded.');
            }

            $location = $post->getUrl();

            if ($this->getRequest()->getParam('unapproved')) {
                $this->messageManager->addSuccess(__('Your comment has been posted and is awaiting moderation.'));
            } elseif ($hash = $this->getRequest()->getParam('_hash')) {
                $post->cleanModelCache();
                
                $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $post]);
                
                $location .= '#' . $hash;
                
                $this->messageManager->addSuccess(__('Your comment has been posted.'));
            }

            return $redirect->setUrl($location);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
        }
        
        return $redirect->setUrl($this->wpUrlBuilder->getHomeUrl());
    }
}
