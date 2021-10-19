<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\User;

class SeoMetaDataProvider implements \FishPig\WordPress\Api\Data\Entity\SeoMetaDataProviderInterface
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\Entity\ViewableInterface $user
    ): void 
    {
        $pageLayout = $resultPage->getLayout();
        $pageConfig = $resultPage->getConfig();

        $pageConfig->setMetaTitle($user->getName());
        $pageConfig->getTitle()->set($user->getName());

        $pageConfig->addRemotePageAsset(
            $user->getUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        if ($pageMainTitle = $pageLayout->getBlock('page.main.title')) {
            $pageMainTitle->setPageTitle($user->getName());
        }
    }
}
