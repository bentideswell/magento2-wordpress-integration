<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Term;

use FishPig\WordPress\Model\Term;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	public function getEntity()
	{
		return $this->getTerm();
	}

	/**
	 * Returns the current Wordpress category
	 * This is just a wrapper for getCurrentCategory()
	 *
	 * @return Fishpig_Wordpress_Model_Post_Categpry
	 */
	public function getTerm()
	{
		if (!$this->hasTerm()) {
			$this->setTerm($this->_registry->registry(Term::ENTITY));
		}
		
		return $this->_getData('term');
	}
	
	/**
	 * Generates and returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Collection
	 */
	protected function _getPostCollection()
	{
		if ($this->getTerm()) {
			return $this->getTerm()->getPostCollection();
		}
		
		return false;
	}
}
