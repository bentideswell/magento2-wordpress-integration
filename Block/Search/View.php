<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
namespace FishPig\WordPress\Block\Search;

class View extends \FishPig\WordPress\Block\Post\PostList\Wrapper\AbstractWrapper
{
	public function getEntity()
	{
		return $this->_registry->registry('wordpress_search');
	}

	/**
	 * Generates and returns the collection of posts
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Post_Collection
	 */
	protected function _getPostCollection()
	{
		$collection = parent::_getPostCollection()->addSearchStringFilter($this->_getParsedSearchString(), array('post_title', 'post_content'));
				
		if ($postTypes = $this->getRequest()->getParam('post_type')) {
			$collection->addPostTypeFilter($postTypes);
		}
		else {
			$collection->addPostTypeFilter(array('post', 'page'));
		}
		
		return $collection;
	}
	
	/**
	 * Retrieve a parsed version of the search string
	 * If search by single word, string will be split on each space
	 *
	 * @return array
	 */
	protected function _getParsedSearchString()
	{
		$words = explode(' ', $this->getSearchTerm());
		
		if (count($words) > 15) {
			$words = array_slice($words, 0, $maxWords);
		}

		foreach($words as $it => $word) {
			if (strlen($word) < 3) {
				unset($words[$it]);
			}
		}

		return $words;
	}
	
	/**
	 * Retrieve the current search term
	 *
	 * @param bool $escape = false
	 * @return string
	 */
	public function getSearchTerm($escape = false)
	{
		return $this->getEntity()->getSearchTerm($escape);
		return urldecode($this->helper('wordpress/router')->getSearchTerm($escape, $this->getSearchVar()));
	}
	
	/**
	 * Retrieve the search variable
	 *
	 * @return string
	 */
	public function getSearchVar()
	{
		return $this->_getData('search_var') ? $this->_getData('search_var') : 's';
	}
}
