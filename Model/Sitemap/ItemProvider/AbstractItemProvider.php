<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

use \FishPig\WordPress\Model\App;
use \FishPig\WordPress\Model\AppFactory;
use \FishPig\WordPress\Model\App\Factory as WpFactory;
/*use \Magento\Sitemap\Model\SitemapItemInterfaceFactory;*/
use \Magento\Store\Model\App\Emulation;
use \Magento\Framework\App\ObjectManager;

abstract class AbstractItemProvider/* implements ItemProviderInterface*/
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
	public function __construct(AppFactory $appFactory, WpFactory $wpFactory, Emulation $emulation)
	{
		$this->appFactory = $appFactory;
		$this->emulation  = $emulation;
		$this->wpFactory  = $wpFactory;
		
		// OM required as SitemapItemInterfaceFactory is not present in Magento 2.2 and below so constructor injection breaks compilation
		$this->itemFactory = ObjectManager::getInstance()->create('Magento\Sitemap\Model\SitemapItemInterfaceFactory');
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
