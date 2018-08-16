<?php
/*
 *
 */	
namespace FishPig\WordPress\Model;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\PostTypeFactory;

class PostTypeManager
{
	/*
	 * @var 
	 */
	protected $moduleManager;
	
	/*
	 * @var 
	 */
	protected $storeManager = false;
	
	/*
	 * @var array
	 */
	protected $types = [];

	/*
	 *
	 * @param  ModuleManaher $moduleManaher
	 * @return void
	 */
	public function __construct(ModuleManager $moduleManager, StoreManagerInterface $storeManager, PostTypeFactory $postTypeFactory)
	{
		$this->moduleManager   = $moduleManager;
		$this->storeManager    = $storeManager;
		$this->postTypeFactory = $postTypeFactory;
		
		$this->load();
	}
	
	/*
	 *
	 *
	 * @return $this
	 */
	public function load()
	{
		$storeId = $this->getStoreId();
		
		if (isset($this->types[$storeId])) {
			return $this;
		}
		
		if ($this->isAddonEnabled()) {
			$this->types[$storeId] = \Magento\Framework\App\ObjectManager::getInstance()
				->get('FishPig\WordPress_PostTypeTaxonomy\Model\Test')
					->getPostTypeData();
		}
		else {
			$this->types[$storeId] = [
				'post' => $this->postTypeFactory->create()->addData([
					'post_type'  => 'post',
					'rewrite'    => array('slug' => $this->getConfig()->getOption('permalink_structure')),
					'taxonomies' => ['category', 'post_tag'],
					'_builtin'   => true,
				]),
				'page' => $this->postTypeFactory->create()->addData([
					'post_type'    => 'page',
					'rewrite'      => ['slug' => '%postname%/'],
					'hierarchical' => true,
					'taxonomies'   => [],
					'_builtin'     => true,
				]),
			];
		}
		
		return $this;
	}
	
	/*
	 *
	 *
	 * @return bool
	 */
	protected function isAddonEnabled()
	{
		return $this->moduleManager->isOutputEnabled('FishPig_WordPress_PostTypeTaxonomy');
	}

	/*
	 *
	 *
	 * @return int
	 */
	protected function getStoreId()
	{
		return (int)$this->storeManager->getStore()->getId();
	}

	/*
	 *
	 *
	 * @return false|Type
	 */
	public function getPostType($type = null)
	{
		if ($types = $this->getPostTypes()) {
			if ($type === null) {
				return $types;
			}
			else if (isset($types[$type])) {
				return $types[$type];
			}
		}
		
		return false;
	}

	/*
	 *
	 *
	 * @return false|array
	 */
	public function getPostTypes()
	{
		$storeId = $this->getStoreId();
		
		$this->load();
		
		return isset($this->types[$storeId]) ? $this->types[$storeId] : false;		
	}
}
