<?php
/**
 *
 */
namespace FishPig\WordPress\Block\User;

use FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use FishPig\WordPress\Model\User;

class View extends AbstractWrapper
{
    /**
     * Caches and returns the current category
     *
     * @return \FishPig\WordPress\Model\User
     */
    public function getEntity()
    {
        return $this->registry->registry(User::ENTITY);
    }

    /**
     * Generates and returns the collection of posts
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    protected function _getPostCollection()
    {
        return parent::_getPostCollection()
            ->addFieldToFilter('post_author', $this->getEntity() ? $this->getEntity()->getId() : 0);
    }
}
