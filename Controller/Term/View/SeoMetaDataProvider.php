<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Term\View;

class SeoMetaDataProvider implements \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\Entity\ViewableInterface $term
    ): void 
    {
        $pageLayout = $resultPage->getLayout();
        $pageConfig = $resultPage->getConfig();

        $pageConfig->setMetaTitle($term->getName());
        $pageConfig->getTitle()->set($term->getName());

        $pageConfig->addRemotePageAsset(
            $term->getUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        if ($pageMainTitle = $pageLayout->getBlock('page.main.title')) {
            $pageMainTitle->setPageTitle($term->getName());
        }
    }
}
