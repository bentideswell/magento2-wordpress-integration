<?php
/*
 * 
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Network;
use FishPig\WordPress\Model\WPConfig;
use Magento\Store\Model\StoreManagerInterface;

class Url
{
	/*
	 * @var OptionManager
	 */
	protected $optionManger;
	
	/*
	 * @var WPConfig
	 */
	protected $wpConfig;
	
	/*
	 * @var Network
	 */
	protected $network;

	/*
	 * @var 
	 */
	protected $storeManager = null;
	
	/*
	 *
	 * @var string
	 */
	protected $magentoUrl = [];
	
	/*
	 * Constructor
	 */
	public function __construct(OptionManager $optionManager, Network $network, WPConfig $wpConfig, StoreManagerInterface $storeManager)
	{
		$this->optionManager = $optionManager;
		$this->wpConfig      = $wpConfig;
		$this->network       = $network;
		$this->storeManager  = $storeManager;
	}

	/*
	 * Get the Magento base URL
	 *
	 * @return string
	  */
	public function getMagentoUrl()
	{
		$store = $this->storeManager->getStore();
		$storeId = (int)$store->getId();
		
		if (!isset($this->magentoUrl[$storeId])) {
			$magentoUrl = rtrim(
				str_ireplace(
					'index.php',
					'',
					$store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
				),
				'/'
			);
	
			if ($store->isUseStoreInUrl()) {
				if (preg_match('/(.*)' . $store->getCode() . '[\/]*$/', $magentoUrl, $matches)) {
					$magentoUrl = $matches[1];
				}
			}
			
			$this->magentoUrl[$storeId] = rtrim($magentoUrl, '/');
		}
		
		return $this->magentoUrl[$storeId];
	}
	
	/*
	 * Get the blog route
	 *
	 * @return string
	  */
	public function getBlogRoute()
	{
		return trim(substr($this->getHomeUrl(), strlen($this->getMagentoUrl())), '/');
	}

	/*
	 * Generate a WordPress frontend URL
	 *
	 * @param string $uri = ''
	 * @return string
	  */
	public function getUrl($uri = '')
	{
		$url = $this->getHomeUrl()	. '/' . $uri;
		
		if (!$this->hasTrailingSlash()) {
			$url = rtrim($url, '/');
		}
		
		return $url;
	}
	
	/*
	 * Determine whether to use a trailing slash on URLs
	 *
	 * @return bool
	 */
	public function hasTrailingSlash()
	{
		if ($permalinkStructure = $this->optionManager->getOption('permalink_structure')) {
			return substr($permalinkStructure, -1) === '/';
		}
		
		return false;
	}
	
	/*
	 * Get the Site URL
	 *
	 * @return string
	  */
	public function getSiteurl($extra = '')
	{
		if (!($siteUrl = $this->wpConfig->getData('WP_SITEURL'))) {
			$siteUrl = $this->optionManager->getOption('siteurl');
		}

		return rtrim(rtrim($siteUrl, '/') . '/' . ltrim($extra, '/'), '/');
	}
	
	/*
	 * Get the Home URL
	 *
	 * @return string
	  */
	public function getHomeUrl()
	{
		if (!($home = $this->wpConfig->getData('WP_HOME'))) {
			$home = $this->optionManager->getOption('home');
		}
		
		return rtrim($home, '/');
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getBaseFileUploadUrl()
	{
		return rtrim($this->getWpContentUrl(), '/') . '/uploads/';
	}

	/*
	 *
	 *
	 * @return 
	 */
	public function getWpContentUrl()
	{
		if (!($contentUrl = $this->wpConfig->getData('WP_CONTENT_URL'))) {
			$contentUrl = $this->getSiteUrl() . '/wp-content/';
		}
		
		return $contentUrl;
	}
	
	/*
	 * Retrieve the upload URL
	 *
	 * @return string
	 */
	public function getFileUploadUrl()
	{
		$url = $this->optionManager->getOption('fileupload_url');
		
		if (!$url) {
			foreach(array('upload_url_path', 'upload_path') as $config) {
				if ($value = $this->optionManager->getOption($config)) {
					if (strpos($value, 'http') === false) {
						if (substr($value, 0, 1) !== '/') {
							$url = $this->getSiteurl() . $value;
						}
					}
					else {
						$url = $value;
					}

					break;
				}
			}
			

			if (!$url) {
				if ($this->network->getBlogId() !== 1) {
					$url = $this->getBaseFileUploadUrl() . 'sites/' . $this->network->getBlogId() . '/';
				}
				else {
					$url = $this->getBaseFileUploadUrl();
				}
			}
		}
		
		return rtrim($url, '/') . '/';
	}
	
	/*
	 *
	 * @return bool
	 */
	public function isRoot()
	{
		return false;
	}

	/*
	 *
	 * @return int
	 */
	protected function getStoreId()
	{
		return (int)$this->storeManager->getStore()->getId();
	}
}
