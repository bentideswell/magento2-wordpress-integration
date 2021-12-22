<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\User\View;

class SeoMetaDataProvider extends \FishPig\WordPress\Controller\Action\SeoMetaDataProvider
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\ViewableModelInterface $user
    ): void {
        parent::addMetaData($resultPage, $user);

        $this->setMetaTitleWithBlogName($user->getName());
        $this->setCanonicalUrl($user->getUrl());
        $this->setPageTitle($user->getName());
    }
}
