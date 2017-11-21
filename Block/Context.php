<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block;

use \FishPig\WordPress\Model\App;
use \FishPig\WordPress\Model\Config;
use \FishPig\WordPress\Model\App\Url;
use \FishPig\WordPress\Model\App\Factory;
use \FishPig\WordPress\Helper\View;
use \FishPig\WordPress\Helper\Plugin;
use \FishPig\WordPress\Helper\Filter;
use \Magento\Framework\Registry;
use \Magento\Framework\ObjectManager\ContextInterface;

class Context implements ContextInterface
{
	/*
	 *
	 *
	 */
	public function __construct(App $app, Config $config, Url $urlBuilder, Factory $factory, View $viewHelper, Plugin $pluginHelper, Registry $registry, Filter $filterHelper)
	{
		$this->_app = $app;
		$this->_config = $config;
		$this->_urlBuilder = $urlBuilder;
		$this->_factory = $factory;
		$this->_viewHelper = $viewHelper;
		$this->_registry = $registry;
		$this->_pluginHelper = $pluginHelper;
		$this->_filterHelper = $filterHelper;
	}	
	
	/*
	 *
	 *
	 * @return
	 */
	public function getApp()
	{
		return $this->_app;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getConfig()
	{
		return $this->_config;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getUrlBuilder()
	{
		return $this->_urlBuilder;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getFactory()
	{
		return $this->_factory;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getViewHelper()
	{
		return $this->_viewHelper;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getRegistry()
	{
		return $this->_registry;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getPluginHelper()
	{
		return $this->_pluginHelper;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getFilterHelper()
	{
		return $this->_filterHelper;
	}
}
