<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Homepage extends AbstractModel implements ViewableInterface
{
	/**
	 * @var string
	**/
	const ENTITY = 'wordpress_homepage';

	/**
	 * @const string
	*/
	const CACHE_TAG = 'wordpress_homepage';
	
	/**
	 * @var
	**/    
    protected $_blogPage = null;
    
	/**
	 *
	 *
	 * @return  string
	**/
	public function getName()
	{
		if ($blogPage = $this->getBlogPage()) {
			return $blogPage->getName();
		}
		
		return $this->_viewHelper->getBlogName();
	}

	/**
	 *
	 *
	 * @return  string
	**/
	public function getUrl()
	{
		if ($blogPage = $this->getBlogPage()) {
			return $blogPage->getUrl();	
		}
		
		return $this->url->getUrl();
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
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getBlogPage()
	{
		if ($this->_blogPage !== null) {
			return $this->_blogPage;
			
		}
		
		$this->_blogPage = false;

		if ((int)$this->_viewHelper->getBlogPageId() > 0) {
			$blogPage = $this->_factory->getFactory('Post')->create()->load(
				$this->_viewHelper->getBlogPageId()
			);
			
			if ($blogPage->getId()) {
				$this->_blogPage = $blogPage;
			}
		}
		
		return $this->_blogPage;
	}
}
