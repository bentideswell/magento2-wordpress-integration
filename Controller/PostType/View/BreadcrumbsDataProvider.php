<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\PostType\View;

class BreadcrumbsDataProvider implements \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface
{
    /**
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return array
     */
    public function getData(\FishPig\WordPress\Api\Data\ViewableModelInterface $postType): array
    {
        $crumbs = [];

        if ($postType->isFrontPage()) {
            return $crumbs;
        }

        if ($postType->getPostType() === 'post') {
            $crumbs['post'] = [
                'label' => __('Blog'),
            ];
        } else {
            $crumbs['post'] = [
                'label' => __($postType->getName()),
            ];
        }

        return $crumbs;
    }
    
    /**
     * @param  string $slugPart
     * @param  \FishPig\WordPress\Model\PostType $postType
     * @return bool
     */
    private function isPostTypeBaseSlug(string $slugPart, \FishPig\WordPress\Model\PostType $postType): bool
    {
        if ($slugPart === $postType->getPostType()) {
            return true;
        }
        
        if ($postType->getArchiveSlug()) {
            return trim($postType->getArchiveSlug(), '/') === $slugPart;
        }
        
        return false;
    }
}
