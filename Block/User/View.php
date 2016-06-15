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
	 * @return \FishPig\WordPress\Model\User
	 */
	public function getEntity()
	{
		return $this->_registry->registry(\FishPig\WordPress\Model\User::ENTITY);
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
	 */
	protected function _getPostCollection()
	{
		return parent::_getPostCollection()->addFieldToFilter('post_author', $this->getEntity()->getId());
	}
}
