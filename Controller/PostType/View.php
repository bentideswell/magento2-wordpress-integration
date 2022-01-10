<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\PostType;

use Magento\Framework\Controller\ResultFactory;
use FishPig\WordPress\Model\PostType;
use Magento\Framework\Exception\NoSuchEntityException;

class View extends \FishPig\WordPress\Controller\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     * @param \FishPig\WordPress\Model\PostRepository $postRepository,
     * @param \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider,
        \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider
    ) {
        $this->postTypeRepository = $postTypeRepository;
        $this->seoMetaDataProvider = $seoMetaDataProvider;
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;

        parent::__construct($context, $wpContext);
    }
    
    /**
     *
     */
    public function execute()
    {
        $request = $this->getRequest();
        
        // This will throw Exception is post does not exist
        $postType = $this->postTypeRepository->get($request->getParam('post_type'));

        $this->registry->register($postType::ENTITY, $postType);

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, $this->getLayoutHandles($postType));

        $this->seoMetaDataProvider->addMetaData($resultPage, $postType);

        $this->addBreadcrumbs(
            $this->breadcrumbsDataProvider->getData($postType)
        );

        return $resultPage;
    }

    /**
     * @param  PostType $postType
     * @return array
     */
    private function getLayoutHandles(PostType $postType): array
    {
        $layoutHandles = [
            'wordpress_post_type_view',
            'wordpress_post_type_' . $postType->getPostType() . '_view',
            'wordpress_post_type_' . $postType->getPostType() . '_list',
            'wordpress_' . $postType->getPostType() . '_list',
            'wordpress_posttype_view', // Legacy
        ];

        if ($postType->isFrontPage()) {
            $layoutHandles[] = 'wordpress_front_page';
        }
        
        if ($postType->getPostType() === 'post') {
            $layoutHandles[] = 'wordpress_homepage_view'; // Legacy
        }

        return $layoutHandles;
    }

    /**
     * @return int
     */
    private function getPreviewId()
    {
        return $this->_request->getParam('elementor-preview') ?? 0;
    }
}
