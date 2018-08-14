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
use \FishPig\WordPress\Model\App\WPConfig;

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

	public function __construct(Reader $reader, ScopeConfigInterface $scopeConfig, ResourceConnection $resourceConnection, CustomerSession $customerSession, StoreManagerInterface $storeManager, WPConfig $wpConfig)
	{
		$this->reader = $reader;
		$this->scopeConfig = $scopeConfig;
		$this->resourceConnection = $resourceConnection;
		$this->customerSession = $customerSession;
		$this->storeManager = $storeManager;
		
		$wpConfig;
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
		return \Magento\Framework\App\ObjectManager::getInstance()->get('FishPig\WordPress\Model\Option')->getOption($key);
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
