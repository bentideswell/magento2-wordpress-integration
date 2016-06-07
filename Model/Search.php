<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Search extends \Magento\Framework\DataObject implements ViewableInterface
{
	/**
	 * @var string
	**/
	const ENTITY = 'wordpress_search';
	const VAR_NAME = 's';
	
	protected $_app = null;
	protected $_config = null;
	protected $_request = null;
	protected $_wpUrlBuilder = null;
	
    public function __construct(
    	\FishPig\WordPress\Model\App $app, 
    	\FishPig\WordPress\Model\Config $config,
    	\FishPig\WordPress\Model\App\Url $wpUrlBuilder,
    	\Magento\Framework\App\Request\Http $request,
    	array $data = []
    )
    {
	    parent::__construct($data);
	    
	    $this->_app = $app;
	    $this->_config = $config;
	    $this->_wpUrlBuilder = $wpUrlBuilder;
	    $this->_request = $request;
    }
    
    public function getApp()
    {
	    return $this->_app;
    }
    
    public function getSearchTerm()
    {
		return $this->_request->getParam(self::VAR_NAME);
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
		return $this->_wpUrlBuilder->getUrl();
	}
		
	/**
	 *
	 *
	 * @return  string
	**/
	public function getContent()
	{
		return '';
	}
	
	/**
	 *
	 *
	 * @return \FishPig\WordPress\Model\Image
	**/
	public function getImage()
	{
		return false;
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getPageTitle()
	{
		return $this->getName();
	}

	/**
	 *
	 *
	 * @return  string
	**/	
	public function getMetaDescription()
	{
		return $this->getName();
	}

	/**
	 *
	 *
	 * @return  string
	**/
	public function getMetaKeywords()
	{
		return 'blog,homepage,wordpress,fishpig';
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getRobots()
	{
		return 'noindex,follow';
	}
	
	/**
	 *
	 *
	 * @return  string
	**/
	public function getCanonicalUrl()
	{
		return $this->getUrl();
	}
}
