<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post;

class Password extends \Magento\Framework\App\Action\Action
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Model\Post\PasswordManager $postPasswordManager
    ) {
        $this->postRepository = $postRepository;
        $this->postPasswordManager = $postPasswordManager;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        try {
            $post = $this->postRepository->get(
                (int)$this->getRequest()->getPost('post')
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return $this->_forward('noRoute');
        }

        $this->postPasswordManager->setPostPassword(
            $this->getRequest()->getPost('post_password', null)
        );
        
        return $this->resultFactory->create(
            $this->resultFactory::TYPE_REDIRECT
        )->setUrl(
            $post->getUrl()
        )->setHttpResponseCode(
            302
        );
    }
}
