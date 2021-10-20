<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Search\View;

class SeoMetaDataProvider implements \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\Entity\ViewableInterface $search
    ): void 
    {
        $pageLayout = $resultPage->getLayout();
        $pageConfig = $resultPage->getConfig();

        $pageConfig->setMetaTitle($search->getName());
        $pageConfig->getTitle()->set($search->getName());

        $pageConfig->addRemotePageAsset(
            $search->getUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        if ($pageMainTitle = $pageLayout->getBlock('page.main.title')) {
            $pageMainTitle->setPageTitle($search->getName());
        }
    }
}
