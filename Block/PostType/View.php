<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\PostType;

use FishPig\WordPress\Model\PostType;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     * @var PostType
     */
    private $postType = null;
    
    /**
     * @return PostType
     */
    public function getPostType(): PostType
    {
        if ($this->postType === null) {
            $this->postType = $this->registry->registry(PostType::ENTITY) ?? false;
        }
        
        return $this->postType;

    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function getBasePostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        $collection = $this->getPostType()->getPostCollection();
        
        if ($this->getPostType()->isFrontPage()) {
            $collection->addStickyPostsToCollection();
        }

        return $collection;
    }
}
