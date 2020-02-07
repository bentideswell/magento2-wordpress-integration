<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Sidebar\Widget;

class Pages extends AbstractWidget
{
    /**
     * Returns the currently loaded page model
     *
     * @return FishPig\WordPress\Model\Post
     */
    public function getPost()
    {
        if (!$this->hasPost()) {
            $this->setPost(false);

            if ($post = $this->registry->registry('wordpress_post')) {
                if ($post->getPostType() === 'page') {
                    $this->setPost($post);
                }
            }    
        }

         return $this->_getData('post');
    }

    /**
     * Retrieve a collection  of pages
     *
     * @return FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPages()
    {
        return $this->getPosts();
    }

    public function getPosts()
    {
        $posts = $this->factory->create('Model\ResourceModel\Post\Collection')->addPostTypeFilter('page');

        if ($this->hasParentId()) {
            $posts->addPostParentIdFilter($this->getParentId());
        }
        else if ($this->getPost() && $this->getPost()->hasChildren()) {
            $posts->addPostParentIdFilter($this->getPost()->getId());
        }
        else {
            $posts->addPostParentIdFilter(0);
        }

        return $posts->addIsViewableFilter()->load();
    }

    /**
     * Retrieve the block title
     *
     * @return string
     */
    public function getTitle()
    {
        if ($this->getPost() && $this->getPost()->hasChildren()) {
            return $this->getPost()->getPostTitle();
        }

        return parent::getTitle();
    }

    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return __('Pages');
    }

    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('sidebar/widget/pages.phtml');
        }

        return parent::_beforeToHtml();
    }
}
