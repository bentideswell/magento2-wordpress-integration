<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\PostType\View;

class SeoMetaDataProvider extends \FishPig\WordPress\Controller\Action\SeoMetaDataProvider
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\ViewableModelInterface $postType
    ): void {
        parent::addMetaData($resultPage, $postType);

        if ($postType->getPostType() === 'post') {
            $blogName = $this->getBlogInfo()->getBlogName();

            $this->setMetaTitle($blogName);
            $this->setPageTitle($blogName);
            $this->setCanonicalUrl($postType->getUrl());
        } else {
            $this->setPageTitle($postType->getName());
            $this->setCanonicalUrl($postType->getUrl());

        }
    }
}
