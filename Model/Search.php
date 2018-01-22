<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Search extends AbstractModel implements ViewableInterface
{
	/*
	 * @const string
	 */
	const ENTITY = 'wordpress_search';
	
	/*
	 * @const string
	 */
	const VAR_NAME = 's';

	/*
	 * @const string
	 */
	const VAR_NAME_POST_TYPE = 'post_type';
	
	/*
	 * Get the search term
	 *
	 * @return  string
	 */
  public function getSearchTerm()
  {
		return $this->_viewHelper->getRequest()->getParam(self::VAR_NAME);
  }

	/*
	 * Get the name of the search
	 *
	 * @return  string
	 */
	public function getName()
	{
		return 'Search results for ' . $this->getSearchTerm();
	}

	/*
	 * Get an array of post types
	 *
	 * @return array
	 */
	public function getPostTypes()
	{
		return $this->_viewHelper->getRequest()->getParam(self::VAR_NAME_POST_TYPE);	
	}
	
	/*
	 *
	 *
	 * @return  string
	 */
	public function getUrl()
	{
		$extra = '';
		
		if ($postTypes = $this->getPostTypes()) {
			foreach($postTypes as $postType) {
				$extra .= self::VAR_NAME_POST_TYPE . '[]=' . urlencode($postType) . '&';
			}
			
			$extra = '?' . rtrim($extra, '&');
		}
		
		return $this->_wpUrlBuilder->getUrl() . 'search/' . urlencode($this->getSearchTerm()) . '/' . $extra;
	}
}
