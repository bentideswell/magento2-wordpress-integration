<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Search;

class View extends \FishPig\WordPress\Controller\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     * $param \FishPig\WordPress\Model\Search $search,
     * @param \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider
     * @param \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\Search $search,
        \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider,
        \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider
    ) {
        $this->search = $search;
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

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, ['wordpress_search_view']);

        $this->seoMetaDataProvider->addMetaData($resultPage, $this->search);
        
        $this->addBreadcrumbs(
            $this->breadcrumbsDataProvider->getData($this->search)
        );

        return $resultPage;
    }
}
