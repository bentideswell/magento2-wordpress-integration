<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\User;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	/**
	 * Caches and returns the current category
	 *
	 * @return Fishpig_Wordpress_Model_User
	 */
	public function getEntity()
	{
		return $this->_registry->registry(\FishPig\WordPress\Model\User::ENTITY);
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		return parent::_getPostCollection()->addFieldToFilter('post_author', $this->getEntity()->getId());
	}
}
