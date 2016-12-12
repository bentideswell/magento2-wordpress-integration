<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Homepage;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	/**
	 * @return
	 **/
	public function getEntity()
	{
		return $this->_registry->registry('wordpress_homepage');
	}
	
	/**
	 * Retrieve the tag line set in the WordPress Admin
	 *
	 * @return string
	 */
	public function getIntroText()
	{
		return trim($this->getEntity()->getContent());
	}
	
	/**
	 * Returns the blog homepage URL
	 *
	 * @return string
	 */
	public function getBlogHomepageUrl()
	{
		return $this->getEntity()->getUrl();
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
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		return parent::_getPostCollection()
			->addStickyPostsToCollection()
			->addPostTypeFilter('post');
	}
}
