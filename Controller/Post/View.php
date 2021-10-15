<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Post;

use Magento\Framework\Controller\ResultFactory;
use FishPig\WordPress\Model\Post;

class View extends \FishPig\WordPress\Controller\Action
{

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface $seoMetaDataProvider
    ) {
        $this->postRepository = $postRepository;
        $this->seoMetaDataProvider = $seoMetaDataProvider;
        parent::__construct($context, $wpContext);
    }
    
    /**
     *
     */
    public function execute()
    {
        // Load the post
        $post = $this->postRepository->get(
            (int)$this->getRequest()->getParam('id')
        );
        
        $this->registry->register($post::ENTITY, $post);

        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, $this->getLayoutHandles($post));

        $this->seoMetaDataProvider->addMetaData($resultPage, $post);
        
        echo __LINE__;exit;
    }


    /**
     * @param  Post $post
     * @return array
     */
    public function getLayoutHandles(Post $post): array
    {
        $postType = $post->getPostType();
        $template = $post->getMetaValue('_wp_page_template');
        
        if ($postType == 'revision' && $post->getParentPost()) {
            $postType = $post->getParentPost()->getPostType();
            
            if (!$template) {
                $template = $post->getParentPost()->getMetaValue('_wp_page_template');
            }
        }

        $layoutHandles = ['wordpress_post_view_default'];

        if ($post->isFrontPage()) {
            $layoutHandles[] = 'wordpress_front_page';
        }

        $layoutHandles[] = 'wordpress_' . $postType . '_view';
        $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $post->getId();

        if ($template) {
            $templateName = str_replace('.php', '', $template);

            $layoutHandles[] = 'wordpress_post_view_' . $templateName;
            $layoutHandles[] = 'wordpress_post_view_' . $templateName . '_' . $post->getId();

            if ($postType !== 'post') {
                $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName;
                $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName . '_' . $post->getId();
            }
        }

        if ($post->getParentId()) {
            $layoutHandles[] = 'wordpress_' . $postType . '_view_parent_' . $post->getParentId();
        }

        if ($urlKey = preg_replace('/[^a-z0-9]+/', '_', strtolower(rtrim($post->getPermalink(), '/')))) {
            $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $urlKey;
        }

        return $layoutHandles;
    }
    
    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return (int)$this->getRequest()->getParam('id');
    }
    
    /**
     * @return bool
     */
    protected function _canPreview()
    {
        return true;
    }

    /**
     * @return
     */
    protected function _getForward()
    {
        if ($entity = $this->_getEntity()) {
            if ($entity->isFrontPage()) {
                if ((int)$this->getRequest()->getParam('is_front') === 0) {
                    return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($this->url->getHomeUrl());
                } elseif (strpos($this->_url->getCurrentUrl(), 'is_front/1') !== false) {
                    $realUrl = $entity->getUrl();

                    if (strpos($realUrl, 'is_front/1') === false) {
                        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setUrl($realUrl);
                    }
                }

                // Request is static homepage (page) with a preview set (maybe visual editor)
                foreach (['p', 'page_id', 'preview_id'] as $paramKey) {
                    if ($previewId = (int)$this->getRequest()->getParam($paramKey)) {
                        $previewPost = $this->factory->create('Post')->load($previewId);

                        if ($previewPost->getId() && !$previewPost->isPublished()) {
                            $this->getRequest()->setParam('preview_id', $previewPost->getId());

                            $this->registry->unregister($previewPost::ENTITY);

                            return $this->resultFactory
                                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
                                ->setModule('wordpress')
                                ->setController('post')
                                ->forward('preview');
                        }
                    }
                }
            }

            if ($entity->getPostStatus() === 'private' && !$this->wpContext->getCustomerSession()->isLoggedIn()) {
                return $this->_getNoRouteForward();
            }
        }

        return parent::_getForward();
    }

    /**
     *
     */
    protected function _initLayout()
    {
        parent::_initLayout();

        $commentId = (int)$this->getRequest()->getParam('comment-id');
        $commentStatus = (int)$this->getRequest()->getParam('comment-status');
        $unapproved = (int)$this->getRequest()->getParam('unapproved');
        
        if ($unapproved > 0 || ($commentId > 0 && $commentStatus === 0)) {
            $this->messageManager->addSuccess(__('Your comment has been posted and is awaiting moderation.'));
        } elseif ($commentId > 0) {
            $this->messageManager->addSuccess(__('Your comment has been posted.'));
        }

        return $this;
    }

    /**
     * Get the blog breadcrumbs
     *
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        if ($this->_getEntity()->isFrontPage()) {
            return [];
        }

        $crumbs = parent::_getBreadcrumbs();

        // Handle post type breadcrumb
        $postType = $this->getEntityObject()->getTypeInstance();

        if ($crumbObjects = $postType->getBreadcrumbStructure($this->getEntityObject())) {
            foreach ($crumbObjects as $crumbType => $crumbObject) {
                $crumbs[$crumbType] = [
                    'label' => (string)__($crumbObject->getName()),
                    'title' => (string)__($crumbObject->getName()),
                    'link' => $crumbObject->getUrl(),
                ];
            }
        }

        $crumbs['post'] = [
            'label' => (string)__($this->_getEntity()->getName()),
            'title' => (string)__($this->_getEntity()->getName())
        ];

        return $crumbs;
    }

    
    /**
     *
     */
    protected function _afterExecute()
    {
        if ($post = $this->getEntityObject()) {
            if ($post->isContentBlock()) {
                $this->getPage()->getConfig()->setRobots('NOINDEX,NOFOLLOW');
            }
        }

        return $this;
    }
}
