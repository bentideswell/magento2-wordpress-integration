<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <ben@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model;

class Config
{
	protected $_reader = null;
	protected $_db = null;
	protected $_scopeConfig = null;
	protected $_customerSession = null;
	protected $_options = array();

	public function __construct(
		\FishPig\WordPress\Model\Config\Reader $reader,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\FishPig\WordPress\Model\App\ResourceConnection $resourceConnection,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Store\Model\StoreManagerInterface $storeManager
	)
	{
		$this->_reader = $reader;
		$this->_scopeConfig = $scopeConfig;
		$this->_resource = $resourceConnection;
		$this->_customerSession = $customerSession;
		$this->_storeManager = $storeManager;
	} 

	/**
	 * @return string
	 **/
	public function getStoreConfigValue($key)
	{
		return $this->_scopeConfig->getValue(
			$key,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->_storeManager->getStore()->getId()
		);
	}

	/**
	 * @return string
	 **/
	public function getStoreConfigFlag($key)
	{
		return (int)$this->_scopeConfig->getValue(
			$key,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
			$this->_storeManager->getStore()->getId()
		) === 1;
	}

	/**
	 * Get a WordPress option value
	 *
	 * @return mixed
	 */
	public function getOption($key)
	{
		if (!isset($this->_options[$key])) {
			$select = $this->_resource->getConnection()->select()
				->from($this->_resource->getTable('wordpress_option'), 'option_value')
				->where('option_name = ?', $key);

			$this->_options[$key] = $this->_resource->getConnection()->fetchOne($select);
		}

		return $this->_options[$key];
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
		if ($config = $this->_reader->getValue('database/tables/table')) {
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
		if ($config = $this->_reader->getValue('shortcodes')) {
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
		if ($config = $this->_reader->getValue('sidebar/widgets')) {
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
		return $this->_customerSession->isLoggedIn();
	}

	/**
	 * Get the Reader object
	 *
	 * @return \FishPig\WordPress\Model\Config\Reader
	 **/
	public function getReader()
	{
		return $this->_reader;
	}

	/**
	 * @return string
	 **/
	public function getLocaleCode()
	{
		return $this->_storeManager->getStore()->getLocaleCode();
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
