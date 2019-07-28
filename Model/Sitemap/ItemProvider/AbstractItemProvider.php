<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

/* Constructor Args */
use FishPig\WordPress\Model\Factory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

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
	
	/**
   * @var ScopeConfigInterface
   */
  protected $scopeConfig;
	
	/*
	 *
	 *
	 */
	public function __construct(Factory $factory, Emulation $emulation, StoreManagerInterface $storeManager, ScopeConfigInterface $scopeConfig)
	{
		$this->emulation    = $emulation;
		$this->factory      = $factory;
		$this->storeManager = $storeManager;
    $this->scopeConfig  = $scopeConfig;
    
		// OM required as SitemapItemInterfaceFactory is not present in Magento 2.2 and below so constructor injection breaks compilation
		$this->itemFactory = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sitemap\Model\SitemapItemInterfaceFactory');
	}
	
	/*
	 *
	 *
	 * @param int $storeId
	 */
	final public function getItems($storeId)
	{
		try {
  		$items = [];
  		
			$this->emulation->startEnvironmentEmulation($storeId);

      if ($this->isEnabledForStore($storeId)) {
  			$items = $this->_getItems($storeId);
  		}
			
			$this->emulation->stopEnvironmentEmulation();
		
			return $items;
		}
		catch (\Exception $e) {
			$this->emulation->stopEnvironmentEmulation();
			
			throw $e;
		}
	}
	
	/**
   *
   *
   * @param int $storeId
   * @return bool
   */
	protected function isEnabledForStore($storeId)
	{
  	return (int)$this->scopeConfig->getValue('wordpress/xmlsitemap/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) === 1;
	}
}
