<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
	public function __construct(
    	\FishPig\WordPress\Model\App $app,
    	\FishPig\WordPress\Model\Config $config,
    	\FishPig\WordPress\Model\App\Url $urlBuilder,
    	\FishPig\WordPress\Model\App\Factory $factory,
    	\FishPig\WordPress\Helper\View $viewHelper,
		\Magento\Framework\Registry $registry
	)
	{
		$this->_app = $app;
		$this->_config = $config;
		$this->_urlBuilder = $urlBuilder;
		$this->_factory = $factory;
		$this->_viewHelper = $viewHelper;
		$this->_registry = $registry;
	}	
	
	public function getApp()
	{
		return $this->_app;
	}
	
	public function getConfig()
	{
		return $this->_config;
	}
	
	public function getUrlBuilder()
	{
		return $this->_urlBuilder;
	}
	
	public function getFactory()
	{
		return $this->_factory;
	}
	
	public function getViewHelper()
	{
		return $this->_viewHelper;
	}
	
	public function getRegistry()
	{
		return $this->_registry;
	}
}


