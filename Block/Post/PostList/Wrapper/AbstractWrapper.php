<?php
/*
 *
 */
namespace FishPig\WordPress\Block\Post\PostList\Wrapper;

/* Parent Class */
use FishPig\WordPress\Block\AbstractBlock;
#use Magento\Framework\View\Element\Template;

abstract class AbstractWrapper extends AbstractBlock
{
	/*
	 *
	 *
	 */
	abstract public function getEntity();
  
	/*
	 *
	 *
	 */	
	protected function _prepareLayout()
	{
		if ($this->getEntity()) {
			$this->getEntity()->applyPageConfigData($this->pageConfig);
		}

		return parent::_prepareLayout();
	}

	/*
	 *
	 *
	 */
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
		return $this->factory->create('Model\ResourceModel\Post\Collection');
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
	
	/*
	 * Gets the post list block
	 *
	 * @return \FishPig\WordPress\Block\ListPost
	 */
	public function getPostListBlock()
	{
		if (!($postListBlock = $this->getChildBlock('wp.post.list'))) {
			$postListBlock = $this->getLayout()
				->createBlock('FishPig\WordPress\Block\Post\ListPost')
				->setTemplate('FishPig_WordPress::post/list.phtml');
				
				$this->setChild('wp.post.list', $postListBlock);
		}

		if ($postListBlock && !$postListBlock->getWrapperBlock()) {
			$postListBlock->setWrapperBlock($this);
		}

		return $postListBlock;
	}
}
