<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post;

use \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper;

class ListPost extends \FishPig\WordPress\Block\Post
{
	/**
	 * Cache for post collection
	 *
	 * @var Fishpig_Wordpress_Model_Resource_Post_Collection
	 */
	protected $_postCollection = null;
	
	/**
	 * Returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
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
	 * @return Fishpig_Wordpress_Block_Post_List_Renderer
	 */
	public function renderPost(\FishPig\WordPress\Model\Post $post)
	{
		if ($original = $this->_registry->registry($post::ENTITY)) {
			$this->_registry->unregister($post::ENTITY);
		}
		
		$this->_registry->register($post::ENTITY, $post);	

		$html = $this->getChildHtml('renderer', false);

		$this->_registry->unregister($post::ENTITY);
		
		if ($original) {
			$this->_registry->register($post::ENTITY, $original);	
		}

		return $html;
	}
	
	/**
	 * Hack required to get containers to clear the cache
	 *
	 * @param string $alias = ''
	 * @param bool $useCache = true
	 * @return string
	**/
	public function getChildHtml($alias = '', $useCache = true)
	{
		if (!$useCache && $alias !== '') {	
			$childName = $this->getLayout()->getChildName($this->getNameInLayout(), $alias);
			
			if (!$childName) {
				$childName = $alias;
			}
			
			$this->_clearCacheOnContainers($childName);
		}
		
		return parent::getChildHtml($alias, $useCache);
	}
	
	/**
	 * This method clears the block cache on child containers.
	 * It does this by going through each block and regenerating the HTML
	 * When the container is loaded, it always use the cache
	 * As the cache has been regenerated with the correct content, this is okay
	 *
	 * @param string $containerName
	 * @return $this
	**/
	protected function _clearCacheOnContainers($blockName)
	{
		$layout = $this->getLayout();
		
		if ($childNames = $layout->getChildNames($blockName)) {
			foreach($childNames as $childName) {
				if ($layout->isBlock($childName)) {
					$layout->renderElement($childName, false);
				}
				else {
					$this->_clearCacheOnContainers($childName);
					$layout->renderElement($childName, false);
				}
			}
		}
		
		return $this;
	}
}
