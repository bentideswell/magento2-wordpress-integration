<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Post\PostList\Wrapper;

abstract class AbstractWrapper extends \FishPig\WordPress\Block\AbstractBlock
{
	abstract public function getEntity();
	
	protected function _prepareLayout()
	{
		$this->_viewHelper->applyPageConfigData($this->pageConfig, $this->getEntity());

		return parent::_prepareLayout();
	}
	
	public function getIntroText()
	{
		return $this->getEntity()->getContent();
	}
	
	/**
	 * Returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	public function getPostCollection()
	{
		if (!$this->hasPostCollection()  && ($collection = $this->_getPostCollection()) !== false) {
			$collection->addIsViewableFilter()->addOrder('post_date', 'desc');
			
			$this->setPostCollection($collection);
			
			$collection->setFlag('after_load_event_name', $this->_getPostCollectionEventName() . '_after_load');
			$collection->setFlag('after_load_event_block', $this);

#			Mage::dispatchEvent('wordpress_post_collection_before_load', array('block' => $this, 'collection' => $collection));
#			Mage::dispatchEvent($this->_getPostCollectionEventName() . '_before_load', array('block' => $this, 'collection' => $collection));
		}

		return $this->_getData('post_collection');
	}
	
	/**
	 * Retrieve the event name for before the post collection is loaded
	 *
	 * @return string
	 */
	protected function _getPostCollectionEventName()
	{
		$class = get_class($this);
		
		return 'wordpress_block_' . strtolower(substr($class, strpos($class, 'Block')+6)) . '_post_collection';
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		return $this->_factory->getFactory('Post')->create()->getCollection();
	}

	/**
	 * Returns the HTML for the post collection
	 *
	 * @return string
	 */
	public function getPostListHtml()
	{
		if (($postListBlock = $this->getPostListBlock()) !== false) {
			return $postListBlock->toHtml();
		}
		
		return '';
	}
	
	/**
	 * Gets the post list block
	 *
	 * @return Fishpig_Wordpress_Block_Post_List|false
	 */
	public function getPostListBlock()
	{
		if ($block = $this->getChildBlock('wp.post.list')) {
			if (!$block->getWrapperBlock()) {
				$block->setWrapperBlock($this);
			}

			return $block;
		}
		
		return false;
	}
}
