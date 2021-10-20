<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Homepage;

class View extends \FishPig\WordPress\Controller\Action
{
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \FishPig\WordPress\Controller\Action\Context $wpContext
     * @param \FishPig\WordPress\Model\PostRepository $postRepository,
     * @param \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface $seoMetaDataProvider
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \FishPig\WordPress\Controller\Action\Context $wpContext,
        \FishPig\WordPress\Model\FrontPage $frontPage
        \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface $seoMetaDataProvider
    ) {
        $this->frontPage = $frontPage;
        $this->seoMetaDataProvider = $seoMetaDataProvider;

        parent::__construct($context, $wpContext);
    }

    /**
     *
     */
    public function execute()
    {
        $request = $this->getRequest();

        if ($previewPostId = (int)$this->getPreviewId()) {
            echo __METHOD__;
            exit;
            return $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
                ->setModule('wordpress')
                ->setController('post')
                ->setParams(['id' => $previewPostId])
                ->forward('view');
        }


        // We got here, we must be good.
        $resultPage = $this->resultFactory->create(
            \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
        );

        $this->addLayoutHandles($resultPage, $this->getLayoutHandles());

        $this->seoMetaDataProvider->addMetaData($resultPage, $term);

        $this->addBreadcrumbs([]);

        return $resultPage;
    }

    /**
     * @return int
     */
    private function getPreviewId()
    {
        return $this->request->getParam('elementor-preview') ?? 0;
    }
}
