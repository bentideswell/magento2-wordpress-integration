<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Homepage;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
    /**
     *
     * @return
     */
    public function getEntity()
    {
        if (!$this->hasEntity()) {
            if ($homepage = $this->registry->registry('wordpress_homepage')) {
                $this->setData('entity', $homepage->getBlogPage() ? $homepage->getBlogPage() : $homepage);
            }
            else {
                $this->setData('entity', false);
            }
        }

        return $this->getData('entity');
    }

    /**
     * Retrieve the tag line set in the WordPress Admin
     *
     * @return string
     */
    public function getIntroText()
    {
        return $this->getEntity() ? trim($this->getEntity()->getBlogDescription()) : '';
    }

    /**
     * Returns the blog homepage URL
     *
     * @return string
     */
    public function getBlogHomepageUrl()
    {
        return $this->getEntity() ? $this->getEntity()->getUrl() : '';
    }

    /**
     * Determine whether the first page of posts are being displayed
     *
     * @return bool
     */
    public function isFirstPage()
    {
        return $this->getRequest()->getParam('page', '1') === '1';
    }

    /**
     * Generates and returns the collection of posts
     *
     * @return 
     */
    protected function _getPostCollection()
    {
        return parent::_getPostCollection()->addStickyPostsToCollection()->addPostTypeFilter('post');
    }
}
