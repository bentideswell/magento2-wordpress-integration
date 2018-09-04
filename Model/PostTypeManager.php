<?php
/*
 *
 */	
namespace FishPig\WordPress\Model;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\PostType;
use FishPig\WordPress\Model\Factory;
use FishPig\WordPress\Model\OptionManager;

class PostTypeManager
{
	/*
	 * @var 
	 */
	protected $moduleManager;
	
	/*
	 * @var 
	 */
	protected $storeManager;
	
	/*
	 *
	 */
	protected $optionManager;
	
	/*
	 * @var array
	 */
	protected $types = [];

	/*
	 *
	 *
	 *
	 */
	public function __construct(
		        ModuleManager $moduleManager, 
		StoreManagerInterface $storeManager, 
        		      Factory $factory, 
		        OptionManager $optionManager
	)
	{
		$this->moduleManager   = $moduleManager;
		$this->storeManager    = $storeManager;
		$this->factory         = $factory;
		$this->optionManager   = $optionManager;
		
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
		
		if ($postTypeData = $this->getPostTypeDataFromAddon()) {
			foreach($postTypeData as $postType) {
				$this->registerPostType(
					$this->factory->create('PostType')->addData($postType)
				);
			}
		}
		else {
			$this->registerPostType(
				$this->factory->create('PostType')->addData([
					'post_type'  => 'post',
					'rewrite'    => ['slug' => $this->optionManager->getOption('permalink_structure')],
					'taxonomies' => ['category', 'post_tag'],
					'_builtin'   => true,
				])
			);
				
			$this->registerPostType(
				$this->factory->create('PostType')->addData([
					'post_type'    => 'page',
					'rewrite'      => ['slug' => '%postname%/'],
					'hierarchical' => true,
					'taxonomies'   => [],
					'_builtin'     => true,
				])
			);
		}
		
		return $this;
	}
	
	public function registerPostType(PostType $postType)
	{
		$storeId = $this->getStoreId();
		
		if (!isset($this->types[$storeId])) {
			$this->types[$storeId] = [];
		}

		$this->types[$storeId][$postType->getPostType()] = $postType;
		
		return $this;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getPostTypeFactory()
	{
		return $this->factory->create('PostTypeFactory');
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
	 * @return bool
	 */
	public function getPostTypeDataFromAddon()
	{
		return false;
	}
}
