<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Homepage extends AbstractModel
{
	/**
	 * @var string
	**/
	const ENTITY = 'wordpress_homepage';
    
	/**
	 *
	 *
	 * @return  string
	**/
	public function getName()
	{
		return $this->_viewHelper->getBlogName();
	}

	/**
	 *
	 *
	 * @return  string
	**/
	public function getUrl()
	{
		return $this->_wpUrlBuilder->getUrl();
	}
		
	/**
	 *
	 *
	 * @return  string
	**/
	public function getContent()
	{
		return $this->_viewHelper->getBlogDescription();
	}
}
