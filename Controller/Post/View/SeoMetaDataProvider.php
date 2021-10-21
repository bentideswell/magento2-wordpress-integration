<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post\View;

class SeoMetaDataProvider extends \FishPig\WordPress\Controller\Action\SeoMetaDataProvider
{
    /**
     * @param  \Magento\Framework\View\Result\Page $resultPage,
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return void
     */
    public function addMetaData(
        \Magento\Framework\View\Result\Page $resultPage,
        \FishPig\WordPress\Api\Data\Entity\ViewableInterface $post
    ): void 
    {
        parent::addMetaData($resultPage, $post);
        
        $this->setMetaTitleWithBlogName($post->getName());
        $this->setCanonicalUrl($post->getUrl());
        $this->setPageTitle($post->getName());

        if ($description = $post->getPostExcerpt(32)) {
            $this->setMetaDescription($description);
        }
    }
}
