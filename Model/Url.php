<?php
/*
 * 
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\Network;
use FishPig\WordPress\Model\WPConfig;
use FishPig\WordPress\Model\Factory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

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
	protected $storeManager;
	
	/*
	 *
	 */
	protected $factory;
	
	/*
	 *
	 * @var string
	 */
	protected $magentoUrl = [];
	
	/*
	 *
	 *
	 */
	protected $front = [];
	
	/*
	 * Constructor
	 */
	public function __construct(OptionManager $optionManager, Network $network, WPConfig $wpConfig, StoreManagerInterface $storeManager, Factory $factory, ScopeConfigInterface $scopeConfig)
	{
		$this->optionManager = $optionManager;
		$this->wpConfig      = $wpConfig;
		$this->network       = $network;
		$this->storeManager  = $storeManager;
		$this->factory       = $factory;
		$this->scopeConfig   = $scopeConfig;
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
	
			/*
			if ($store->isUseStoreInUrl()) {
				if (preg_match('/(.*)' . $store->getCode() . '[\/]*$/', $magentoUrl, $matches)) {
					$magentoUrl = $matches[1];
				}
			}*/
			
			if ($this->ignoreStoreCode()) {
				if (substr_count($magentoUrl, '/') >= 3) {
					$magentoUrl = substr($magentoUrl, 0, strpos($magentoUrl, '/', strpos($magentoUrl, '//')+2));
				}
			}
			
			$this->magentoUrl[$storeId] = rtrim($magentoUrl, '/');
		}
		
		return $this->magentoUrl[$storeId];
	}
	
	/*
	 * Ignore store code
	 *
	 */
	public function ignoreStoreCode()
	{
		return (int)$this->scopeConfig->getValue(
			'wordpress/setup/ignore_store_code', 
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE, 
			(int)$this->storeManager->getStore()->getId()
		) === 1;
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
	 * Generate a WordPress frontend URL with the Front var in it
	 *
	 * @param string $uri = ''
	 * @return string
	  */
	public function getUrlWithFront($uri = '')
	{
  	if ($front = $this->getFront()) {
    	$uri = ltrim($front . '/' . $uri, '/');
  	}

    return $this->getUrl($uri);
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
				$url = $this->getBaseFileUploadUrl();
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
	
	/*
	 * Get front
	 *
	 */
	public function getFront()
	{
		$storeId = $this->getStoreId();
		
		if (!isset($this->front[$storeId])) {
			$this->front[$storeId] = '';
			
			if ($this->isRoot()) {
				$postPermalink = $this->factory->create('Post')->setPostType('post')->getTypeInstance()->getPermalinkStructure();
			
				if (substr($postPermalink, 0, 1) !== '%') {
					$this->front[$storeId] = trim(substr($postPermalink, 0, strpos($postPermalink, '%')), '/');
				}
			}
		}
		
		return $this->front[$storeId];
	}
}
