<?php
/*
 *
 */	
namespace FishPig\WordPress\Model;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\Taxonomy;
use FishPig\WordPress\Model\TaxonomyFactory;
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Network;

class TaxonomyManager
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
	 *
	 */
	protected $network;
	
	/*
	 * @var array
	 */
	protected $taxonomies = [];

	/*
	 *
	 * @param  ModuleManaher $moduleManaher
	 * @return void
	 */
	public function __construct(
		        ModuleManager $moduleManager, 
		StoreManagerInterface $storeManager, 
		      TaxonomyFactory $taxonomyFactory, 
		        OptionManager $optionManager,
		              Network $network)
	{
		$this->moduleManager   = $moduleManager;
		$this->storeManager    = $storeManager;
		$this->taxonomyFactory = $taxonomyFactory;
		$this->optionManager   = $optionManager;
		$this->network         = $network;

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
		
		if (isset($this->taxonomies[$storeId])) {
			return $this;
		}
		
		if ($this->isAddonEnabled()) {
			echo __METHOD__;exit;
			$this->taxonomies[$storeId] = \Magento\Framework\App\ObjectManager::getInstance()
				->get('FishPig\WordPress_PostTypeTaxonomy\Model\Test')
					->getPostTypeData();
		}
		else {
			$bases = array(
				'category' => $this->optionManager->getOption('category_base') ? $this->optionManager->getOption('category_base') : 'category',
				'post_tag' => $this->optionManager->getOption('tag_base') ? $this->optionManager->getOption('tag_base') : 'tag',
			);
	
			$blogPrefix = $this->network->getBlogId() === 1;
			
			if ($blogPrefix) {
				foreach($bases as $baseType => $base) {
					if ($blogPrefix && $base && strpos($base, '/blog') === 0) {
						$bases[$baseType] = substr($base, strlen('/blog'));	
					}
				}
			}

			$this->registerTaxonomy(
				$this->getTaxonomyFactory()->create()->addData([
					'type' => 'category',
					'taxonomy_type' => 'category',
					'labels' => array(
						'name' => 'Categories',
						'singular_name' => 'Category',
					),
					'public' => true,
					'hierarchical' => true,
					'rewrite' => array(
						'hierarchical' => true,
						'slug' => $bases['category'],
					),
					'_builtin' => true,
				])
			);
			
			$this->registerTaxonomy(
				$this->getTaxonomyFactory()->create()->addData([
					'type' => 'post_tag',
					'taxonomy_type' => 'post_tag',
					'labels' => array(
						'name' => 'Tags',
						'singular_name' => 'Tag',
					),
					'public' => true,
					'hierarchical' => false,
					'rewrite' => array(
						'slug' => $bases['post_tag'],
					),
					'_builtin' => true,
				])
			);
		}
		
		return $this;
	}
	
	public function registerTaxonomy(Taxonomy $taxonomy)
	{
		$storeId = $this->getStoreId();
		
		if (!isset($this->taxonomies[$storeId])) {
			$this->taxonomies[$storeId] = [];
		}

		$this->taxonomies[$storeId][$taxonomy->getTaxonomy()] = $taxonomy;
		
		return $this;
	}
	
	/*
	 *
	 *
	 * @return
	 */
	public function getTaxonomyFactory()
	{
		return $this->taxonomyFactory;
	}

	/*
	 *
	 *
	 * @return false|Type
	 */
	public function getTaxonomy($taxonomy = null)
	{
		if ($taxonomies = $this->getTaxonomies()) {
			if ($taxonomy === null) {
				return $taxonomies;
			}
			else if (isset($taxonomies[$taxonomy])) {
				return $taxonomies[$taxonomy];
			}
		}
		
		return false;
	}

	/*
	 *
	 *
	 * @return false|array
	 */
	public function getTaxonomies()
	{
		$storeId = $this->getStoreId();
		
		$this->load();
		
		return isset($this->taxonomies[$storeId]) ? $this->taxonomies[$storeId] : false;		
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
}
