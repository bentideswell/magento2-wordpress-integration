<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Term;

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
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \FishPig\WordPress\Api\Controller\Action\SeoMetaDataProviderInterface $seoMetaDataProvider,
        \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface $breadcrumbsDataProvider
    ) {
        $this->termRepository = $termRepository;
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
        $term = $this->termRepository->get(
            (int)$request->getParam('id')
        );

        $this->registry->register($term::ENTITY, $term);

        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles(
            $resultPage,
            [
                'wordpress_term_view',
                'wordpress_' . $term->getTaxonomy() . '_view',
                'wordpress_' . $term->getTaxonomy() . '_view_' . $term->getId(),
            ]
        );

        $this->seoMetaDataProvider->addMetaData($resultPage, $term);

        $this->addBreadcrumbs(
            $this->breadcrumbsDataProvider->getData($term)
        );

        return $resultPage;
    }
}
