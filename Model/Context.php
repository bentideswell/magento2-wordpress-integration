<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Model\Config;
use \FishPig\WordPress\Helper\View as ViewHelper;
use \FishPig\WordPress\Helper\Filter as FilterHelper;
use \FishPig\WordPress\Helper\Compatibility as CompatibilityHelper;

class Context implements \Magento\Framework\ObjectManager\ContextInterface
{
	/*
	 *
	 *
	 * @return 
	 */
	public function __construct(App $app, App\ResourceConnection $resourceConnection, App\Url $urlBuilder, App\Factory $factory, Config $config, ViewHelper $viewHelper, FilterHelper $filterHelper, CompatibilityHelper $compatibilityHelper)
	{
		$this->app = $app->init();
		$this->resourceConnection = $resourceConnection;
		$this->config = $config;
		$this->urlBuilder = $urlBuilder;
		$this->factory = $factory;
		$this->viewHelper = $viewHelper;
		$this->filterHelper = $filterHelper;
		$this->compatibilityHelper = $compatibilityHelper;
	}	

	/*
	 *
	 *
	 * @return 
	 */
	public function getApp()
	{
		return $this->app;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getResourceConnection()
	{
		return $this->resourceConnection;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getUrlBuilder()
	{
		return $this->urlBuilder;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getFactory()
	{
		return $this->factory;
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getViewHelper()
	{
		return $this->viewHelper;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getFilterHelper()
	{
		return $this->filterHelper;
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getCompatibilityHelper()
	{
		return $this->compatibilityHelper;
	}
}
