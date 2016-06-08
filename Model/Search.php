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
	/**
	 * @var string
	**/
	const ENTITY = 'wordpress_search';
	
	/**
	 * @var string
	**/
	const VAR_NAME = 's';

	/**
	 *
	 *
	 * @return  string
	**/
    public function getSearchTerm()
    {
		return $this->_viewHelper->getRequest()->getParam(self::VAR_NAME);
    }

	/**
	 *
	 *
	 * @return  string
	**/
	public function getName()
	{
		return 'Search results for ' . $this->getSearchTerm();
	}

	/**
	 *
	 *
	 * @return  string
	**/
	public function getUrl()
	{
		return $this->_wpUrlBuilder->getUrl() . 'search/' . urlencode($this->getSearchTerm()) . '/';
	}
}
