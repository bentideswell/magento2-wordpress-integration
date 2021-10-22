<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Archive;

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
        \FishPig\WordPress\Model\ArchiveFactory $archiveFactory,
        \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider,
        \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider
    ) {
        $this->archiveFactory = $archiveFactory;
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
        $archive = $this->archiveFactory->create()->load(
            trim($request->getParam('year') . '/' . $request->getParam('month') . '/' . $request->getParam('day'), '/')
        );

        if (!$archive->getId()) {
            return $this->getNoRouteForward();
        }
        
        $this->registry->register($archive::ENTITY, $archive);

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, ['wordpress_archive_view']);

        $this->seoMetaDataProvider->addMetaData($resultPage, $archive);
        
        $this->addBreadcrumbs(
            $this->breadcrumbsDataProvider->getData($archive)
        );

        return $resultPage;
    }
}
