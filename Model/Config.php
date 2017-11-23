<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Model\Config\Reader;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \FishPig\WordPress\Model\App\ResourceConnection;
use \Magento\Customer\Model\Session as CustomerSession;
use \Magento\Store\Model\StoreManagerInterface;
		
class Config
{
	/*
	 *
	 */
	protected $reader;
	
	/*
	 *
	 */
	protected $db;
	
	/*
	 *
	 */
	protected $scopeConfig;
	
	/*
	 *
	 */
	protected $customerSession;
	
	/*
	 *
	 */
	protected $resourceConnection;
	
	/*
	 *
	 */
	protected $options = array();

	public function __construct(Reader $reader, ScopeConfigInterface $scopeConfig, ResourceConnection $resourceConnection, CustomerSession $customerSession, StoreManagerInterface $storeManager)
	{
		$this->reader = $reader;
		$this->scopeConfig = $scopeConfig;
		$this->resourceConnection = $resourceConnection;
		$this->customerSession = $customerSession;
		$this->storeManager = $storeManager;
	} 

	/**
	 * @return string
	 **/
	public function getStoreConfigValue($key)
	{
		return $this->scopeConfig->getValue(
			$key,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->storeManager->getStore()->getId()
		);
	}

	/**
	 * @return string
	 **/
	public function getStoreConfigFlag($key)
	{
		return (int)$this->scopeConfig->getValue(
			$key,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->storeManager->getStore()->getId()
		) === 1;
	}

	/**
	 * Get a WordPress option value
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		$storeId = $this->storeManager->getStore()->getId();
		
		if (!isset($this->options[$storeId])) {
			$this->options[$storeId] = [];
		}

		if (!isset($this->options[$storeId][$key])) {
			$resource = $this->resourceConnection;
			
			$select = $resource->getConnection()->select()
				->from($resource->getTable('wordpress_option'), 'option_value')
				->where('option_name = ?', $key);

			$this->options[$storeId][$key] = $resource->getConnection()->fetchOne($select);
		}

		return $this->options[$storeId][$key];
	}

	/**
	 * Get a site option.
	 * This is implemented in Multisite
	 *
	 * @param string $key
	 * @return mixed
	 **/
	public function getSiteOption($key)
	{
		return false;
	}

	/**
	 * Get the DB mapping
	 * This is implemented in Multisite
	 *
	 * @param string $when = 'before_connect'
	 * @return array|false
	 **/
	public function getDbTableMapping($when = 'before_connect')
	{
		if ($config = $this->reader->getValue('database/tables/table')) {
			$map = array();

			foreach($config as $key => $value) {
				$value = $value['@attributes'];

				if ($value['when'] === $when) {
					$map[$value['id']] = $value['name'];

					if (isset($value['meta'])) {
							$map[$value['id'] . '_meta'] = $value['meta'];
					}
				}
			}

			if (count($map) > 0) {
					return $map;
			}
		}

		return false;
	}

	/**
	 * Get the network tables
	 * This is implemented in Multisite
	 *
	 * @return array|false
	 **/
	public function getNetworkTables()
	{
		return false;
	}

	/**
	 * Get the configured shortcodes
	 *
	 * @return array|false
	 **/
	public function getShortcodes()
	{
		if ($config = $this->reader->getValue('shortcodes')) {
			$shortcodes = [];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

			foreach((array)$config['shortcode'] as $shortcode) {
				$shortcode = $shortcode['@attributes'];

				$shortcodeInstance = $objectManager->get($shortcode['class']);

				if (!$shortcodeInstance->isPluginEnabled()) {
					continue;
				}

				if (!isset($shortcode['sortOrder'])) {
					$shortcode['sortOrder'] = 9999;
				}

				$sortOrder = (int)$shortcode['sortOrder'];

				if (!isset($shortcodes[$sortOrder])) {
					$shortcodes[$sortOrder] = array();
				}

				$shortcodes[$sortOrder][$shortcode['id']] = $shortcodeInstance;
			}

			ksort($shortcodes, SORT_NUMERIC);

			$final = array();

			foreach($shortcodes as $groupedShortcodes) {
				$final = array_merge($final, $groupedShortcodes);
			}

			return $final;
		}

		return false;
	}

	/**
	 * Get the configured widgets
	 *
	 * @return array|false
	 **/
	public function getWidgets()
	{
		if ($config = $this->reader->getValue('sidebar/widgets')) {
			$widgets = array();

			foreach($config['widget'] as $widget) {
				$widgets[$widget['@attributes']['id']] = $widget['@attributes']['class'];
			}

			return $widgets;
		}

		return false;
	}

	/**
	 * Get the blog ID
	 *
	 * @return int
	 **/
	public function getBlogId()
	{
		return 1;
	}

	/**
	 * Get the site ID
	 *
	 * @return int
	 **/

	public function getSiteId()
	{
		return 1;
	}

	/**
	 * Get the Reader object
	 *
	 * @return false
	 **/
	public function getSiteAndBlogObjects()
	{
		return false;
	}

	/**
	 * Get a value from the blog table
	 *
	 * @param string $key
	 * @return mixed
	 **/
	public function getBlogTableValue($key)
	{
		return false;
	}

	/**
	 * Determine whether the customer is logged in
	 *
	 * @return bool
	 **/
	public function isLoggedIn()
	{
		return $this->customerSession->isLoggedIn();
	}

	/**
	 * Get the Reader object
	 *
	 * @return \FishPig\WordPress\Model\Config\Reader
	 **/
	public function getReader()
	{
		return $this->reader;
	}

	/**
	 * @return string
	 **/
	public function getLocaleCode()
	{
		return $this->storeManager->getStore()->getLocaleCode();
	}
	
  /*
	 *
	 * @return string
	 */
  public function getBlogBreadcrumbsLabel()
  {
	  return __('Blog');
  } 
}
