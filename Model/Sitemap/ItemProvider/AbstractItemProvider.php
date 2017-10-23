<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

use \FishPig\WordPress\Model\App;
use \FishPig\WordPress\Model\AppFactory;
use \FishPig\WordPress\Model\App\Factory as WpFactory;
use \Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use \Magento\Sitemap\Model\SitemapItemInterfaceFactory;
use \Magento\Store\Model\App\Emulation;

abstract class AbstractItemProvider implements ItemProviderInterface
{
	/*
	 * @var \FishPig\WordPress\Model\App
	 */
	protected $appFactory;
	
	/*
	 * @var \FishPig\WordPress\Model\App
	 */
	protected $wpFactory;
	
	/*
	 * @var \Magento\Store\Model\App\Emulation;
	 */
	protected $emulation;
	
	/*
	 *
	 * @param \FishPig\WordPress\Model\App
	 * @param \Magento\Store\Model\App\Emulation
	 */
	public function __construct(AppFactory $appFactory, WpFactory $wpFactory, Emulation $emulation, SitemapItemInterfaceFactory $itemFactory)
	{
		$this->appFactory = $appFactory;
		$this->emulation  = $emulation;
		$this->wpFactory  = $wpFactory;
		$this->itemFactory = $itemFactory;
	}
	
	/*
	 *
	 *
	 * @param int $storeId
	 */
	final public function getItems($storeId)
	{
		try {
			$this->emulation->startEnvironmentEmulation($storeId);
		
			$app = $this->appFactory->create()->init();
			
			$items = $this->_getItems($storeId);
			
			$this->emulation->stopEnvironmentEmulation();
		
			return $items;
		}
		catch (\Exception $e) {
			$this->emulation->stopEnvironmentEmulation();
			
			throw $e;
		}
		
		return array();
	}
}