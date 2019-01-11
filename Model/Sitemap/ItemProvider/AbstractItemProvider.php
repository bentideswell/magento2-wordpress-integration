<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

/* Constructor Args */
use FishPig\WordPress\Model\Factory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/* Misc */
use \Magento\Framework\App\ObjectManager;

abstract class AbstractItemProvider/* implements ItemProviderInterface*/
{	
	/*
	 * @var \FishPig\WordPress\Model\Factory
	 */
	protected $factory;
	
	/*
	 * @var \Magento\Store\Model\App\Emulation;
	 */
	protected $emulation;
	
	/*
	 *
	 *
	 */
	public function __construct(Factory $factory, Emulation $emulation, StoreManagerInterface $storeManager)
	{
		$this->emulation    = $emulation;
		$this->factory      = $factory;
		$this->storeManager = $storeManager;

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
			
			$items = $this->_getItems($storeId);
			
			$this->emulation->stopEnvironmentEmulation();
		
			return $items;
		}
		catch (\Exception $e) {
			$this->emulation->stopEnvironmentEmulation();
			
			throw $e;
		}
		
		return [];
	}
}
