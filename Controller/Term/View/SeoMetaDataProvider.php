<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Term\View;

class SeoMetaDataProvider extends \FishPig\WordPress\Controller\Action\SeoMetaDataProvider
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\ViewableModelInterface $term
    ): void {
        parent::addMetaData($resultPage, $term);

        $this->setMetaTitleWithBlogName($term->getName());
        $this->setPageTitle($term->getName());
        $this->setCanonicalUrl($term->getUrl());
        
        if ($description = $term->getDescription()) {
            $this->setMetaDescription($description);
        }
    }
}
