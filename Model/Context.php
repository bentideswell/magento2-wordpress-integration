<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
	public function __construct(
		\FishPig\WordPress\Model\App $app,
		\FishPig\WordPress\Model\App\ResourceConnection $resourceConnection,
		\FishPig\WordPress\Model\Config $config,
		\FishPig\WordPress\Model\App\Url $urlBuilder,
		\FishPig\WordPress\Model\App\Factory $factory,
		\FishPig\WordPress\Helper\View $viewHelper,
    \FishPig\WordPress\Helper\Filter $filterHelper
	)
	{
		$this->_app = $app->init();
		$this->_resourceConnection = $resourceConnection;
		$this->_config = $config;
		$this->_urlBuilder = $urlBuilder;
		$this->_factory = $factory;
		$this->_viewHelper = $viewHelper;
		$this->_filterHelper = $filterHelper;
	}	
	
	public function getApp()
	{
		return $this->_app;
	}
	
	public function getResourceConnection()
	{
		return $this->_resourceConnection;
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
	
	public function getFilterHelper()
	{
		return $this->_filterHelper;
	}
}


