<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Post;

use FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class ListPost extends \FishPig\WordPress\Block\Post
{
    /**
     * Cache for post collection
     *
     * @var PostCollection
     */
    protected $_postCollection = null;

    /**
     * Returns the collection of posts
     *
     * @return 
     */
    public function getPosts()
    {
        if ($this->_postCollection === null) {
            if ($this->getWrapperBlock()) {
                if ($this->_postCollection = $this->getWrapperBlock()->getPostCollection()) {
                    if ($this->getPostType()) {
                        $this->_postCollection->addPostTypeFilter($this->getPostType());
                    }
                }
            }
            else {
                $this->_postCollection = $this->factory->create('FishPig\WordPress\Model\ResourceModel\Post\Collection');
            }

            if ($this->_postCollection && ($pager = $this->getChildBlock('pager'))) {
                $pager->setPostListBlock($this)->setCollection($this->_postCollection);
            }
        }

        return $this->_postCollection;
    }

    /**
     * Sets the parent block of this block
     * This block can be used to auto generate the post list
     *
     * @param AbstractWrapper $wrapper
     * @return $this
     */
    public function setWrapperBlock(AbstractWrapper $wrapper)
    {
        return $this->setData('wrapper_block', $wrapper);
    }

    /**
     * Get the HTML for the pager block
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Retrieve the correct renderer and template for $post
     *
     * @param \FishPig\WordPress\Model\Post $post
     * @return FishPig\WordPress\Block_Post_List_Renderer
     */
    public function renderPost(\FishPig\WordPress\Model\Post $post)
    {
        // Create post block
        $postBlock = $this->getLayout()->createBlock('FishPig\WordPress\Block\Post')->setPost($post);

        $vendors = [
          $this->getCustomBlogThemeVendor(),
          'FishPig_WordPress',
        ];

        // First try post type specific template then fall back to default
        $templates = [
          'post/list/renderer/' . $post->getPostType() . '.phtml',
          'post/list/renderer/default.phtml',
        ];

        $templatesToTry = [];

        foreach($templates as $template) {
            foreach($vendors as $vendor) {
                if ($vendor) {
                    $templatesToTry[] = $vendor . '::' . $template;
                }
            }
        }

        if ($rendererTemplate = $this->getData('renderer_template')) {
            array_unshift($templatesToTry, $rendererTemplate);
        }

        foreach($templatesToTry as $templateToTry) {
            if ($this->getTemplateFile($templateToTry)) {
                $postBlock->setTemplate($templateToTry);
                break;
            }
        }

        // Get HTML and return
        return $postBlock->toHtml();
    }

    /**
     * @return 
     */
    public function getCustomBlogThemeVendor()
    {
        return false;
    }

    /**
     *
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::post/list.phtml');
        }

        return parent::_beforeToHtml();
    }
}
