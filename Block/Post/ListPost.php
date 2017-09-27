<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post;

use \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;
use \FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class ListPost extends \FishPig\WordPress\Block\Post
{
	/**
	 * Cache for post collection
	 *
	 * @var PostCollection
	 */
	protected $_postCollection = null;
	
	/*
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
				$this->_postCollection = $this->_factory->getFactory('Post')->create()->getCollection();
			}

			if ($this->_postCollection && ($pager = $this->getChildBlock('pager'))) {
				$pager->setPostListBlock($this)->setCollection($this->_postCollection);
			}
		}

		return $this->_postCollection;
	}
	
	/*
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
	
	/*
	 * Retrieve the correct renderer and template for $post
	 *
	 * @param \FishPig\WordPress\Model\Post $post
	 * @return Fishpig_Wordpress_Block_Post_List_Renderer
	 */
	public function renderPost(\FishPig\WordPress\Model\Post $post)
	{
		// Create post block
		$postBlock = $this->getLayout()->createBlock('FishPig\WordPress\Block\Post')->setPost($post);
			
		// First try post type specific template then fall back to default
		$templatesToTry = [
			'FishPig_WordPress::post/list/renderer/' . $post->getPostType() . '.phtml',
			'FishPig_WordPress::post/list/renderer/default.phtml'
		];
		
		foreach($templatesToTry as $templateToTry) {
			if ($this->getTemplateFile($templateToTry)) {
				$postBlock->setTemplate($templateToTry);
				break;
			}
		}

		// Get HTML and return
		return $postBlock->toHtml();
	}
	
	/*
	 *
	 *
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
