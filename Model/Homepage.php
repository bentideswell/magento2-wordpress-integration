<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Homepage extends \Magento\Framework\DataObject implements ViewableInterface
{
	/**
	 * @var string
	**/
	const ENTITY = 'wordpress_homepage';

	protected $_app = null;
	protected $_config = null;
	protected $_wpUrlBuilder = null;
	
    public function __construct(
    	\FishPig\WordPress\Model\App $app, 
    	\FishPig\WordPress\Model\Config $config,
    	\FishPig\WordPress\Model\App\Url $wpUrlBuilder,
    	array $data = []
    )
    {
	    parent::__construct($data);
	    
	    $this->_app = $app;
	    $this->_config = $config;
	    $this->_wpUrlBuilder = $wpUrlBuilder;
    }
    
    public function getApp()
    {
	    return $this->_app;
    }
    
	/**
	 *
	 *
	 * @return  string
	**/
	public function getName()
	{
		if (!$this->hasName()) {
			$this->setName($this->_config->getOption('blogname'));
		}
		
		return $this->_getData('name');
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
		if (!$this->hasContent()) {
			$this->setContent($this->_config->getOption('blogdescription'));
		}
		
		return $this->_getData('content');
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
		return 'homepage meta description';
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
		return 'index,follow';
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
