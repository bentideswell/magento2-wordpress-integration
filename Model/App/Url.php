<?php
/**
 * 
**/
namespace FishPig\WordPress\Model\App;

/**
 * Generate all WordPress URLs 
**/
class Url
{
	/**
	 * @var 
	**/
	protected $_config = null;
	
	/**
	 * @var 
	**/
	protected $_storeManager = null;
	
	/**
	  *
	  **/
	 protected $_pathInfo = null;
	 
	/**
	 * Constructor
	**/
	public function __construct(\FishPig\WordPress\Model\Config $config, \Magento\Store\Model\StoreManagerInterface $storeManager)
	{
		$this->_config = $config;
		$this->_storeManager = $storeManager;
	}

	/**
	 * Get the Magento base URL
	 *
	 * @return string
	 **/
	public function getMagentoUrl()
	{
		return rtrim(
			str_ireplace(
				'index.php',
				'',
				$this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
			),
			'/'
		);
	}
	
	/**
	 * Get the Magento base URL without the store code
	 *
	 * @return string
	 **/
	/*
	public function getMagentoBaseUrl()
	{
		return rtrim(
			str_ireplace(
				'index.php',
				'',
				$this->_config->getStoreConfigValue('web/unsecure/base_url')
			),
			'/'
		);
	}
	*/
	
	/**
	 * Get the blog route
	 *
	 * @return string
	 **/
	public function getBlogRoute()
	{
		return trim(substr($this->getHomeUrl(), strlen($this->getMagentoUrl())), '/');
	}

	/**
	 * Generate a WordPress frontend URL
	 *
	 * @param string $uri = ''
	 * @return string
	 **/
	public function getUrl($uri = '')
	{
		return $this->getHomeUrl()	. '/' . $uri;
	}
	
	/**
	 * Get the Site URL
	 *
	 * @return string
	 **/
	public function getSiteurl($extra = '')
	{
		return rtrim(rtrim($this->_config->getOption('siteurl'), '/') . '/' . ltrim($extra, '/'), '/');
	}
	
	/**
	 * Get the Home URL
	 *
	 * @return string
	 **/
	public function getHomeUrl()
	{
		return rtrim($this->_config->getOption('home'), '/');
	}

	/**
	 * Retrieve the upload URL
	 *
	 * @return string
	 */
	public function getFileUploadUrl()
	{
		$url = $this->_config->getOption('fileupload_url');
		
		if (!$url) {
			foreach(array('upload_url_path', 'upload_path') as $config) {
				if ($value = $this->_config->getOption($config)) {
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
				if ($this->_config->getBlogId() !== 1) {
					$url = $this->getBaseFileUploadUrl() . 'sites/' . $this->_config->getBlogId() . '/';
				}
				else {
					$url = $this->getBaseFileUploadUrl();
				}
			}
		}
		
		return rtrim($url, '/') . '/';
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getBaseFileUploadUrl()
	{
		return $this->getSiteUrl() . '/wp-content/uploads/';
	}

	/**
	 *
	 *
	 * @return string
	**/
	public function getPathInfo(\Magento\Framework\App\RequestInterface $request)
	{
		if (null === $this->_pathInfo) {	
			$pathInfo = strtolower(trim($request->getPathInfo(), '/'));
			
			if ($magentoUrlPath = parse_url($this->getMagentoUrl(), PHP_URL_PATH)) {
				$magentoUrlPath = ltrim($magentoUrlPath, '/');
				
				if (strpos($pathInfo, $magentoUrlPath) === 0) {
					$pathInfo = ltrim(substr($pathInfo, strlen($magentoUrlPath)), '/');
				}
				
			}
			
			$this->_pathInfo = $pathInfo;
		}
		
		return $this->_pathInfo;
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function getUrlAlias(\Magento\Framework\App\RequestInterface $request)
	{
		$pathInfo = $this->getPathInfo($request);
		$blogRoute = $this->getBlogRoute();

		if ($blogRoute && strpos($pathInfo, $blogRoute) !== 0) {
			return false;
		}

		if (trim(substr($pathInfo, strlen($blogRoute)), '/') === '') {
			return $pathInfo;
		}		
		
		$pathInfo = explode('/', $pathInfo);
		
		// Clean off pager and feed parts
		if (($key = array_search('page', $pathInfo)) !== false) {
			if (isset($pathInfo[($key+1)]) && preg_match("/[0-9]{1,}/", $pathInfo[($key+1)])) {
				$request->setParam('page', $pathInfo[($key+1)]);
				unset($pathInfo[($key+1)]);
				unset($pathInfo[$key]);
				
				$pathInfo = array_values($pathInfo);
			}
		}
		
		/*
		// Clean off feed and trackback variable
		if (($key = array_search('feed', $pathInfo)) !== false) {
			unset($pathInfo[$key]);
			
			if (isset($pathInfo[$key+1])) {
				unset($pathInfo[$key+1]);
			}

			$request->setParam('feed', 'rss2');
			$request->setParam('feed_type', 'rss2');
		}
		*/
		
		// Remove comments pager variable
		foreach($pathInfo as $i => $part) {
			$results = array();
			if (preg_match("/" . sprintf('^comment-page-%s$', '([0-9]{1,})') . "/", $part, $results)) {
				if (isset($results[1])) {
					unset($pathInfo[$i]);
				}
			}
		}
		
		if (count($pathInfo) == 1 && preg_match("/^[0-9]{1,8}$/", $pathInfo[0])) {
			$request->setParam('p', $pathInfo[0]);
			
			array_shift($pathInfo);
		}

		$uri = urldecode(implode('/', $pathInfo));

		return $uri;
	}
	
	/**
	 * Retrieve the blog URI
	 * This is the whole URI after blog route
	 *
	 * @return string
	 */
	public function getRouterRequestUri(\Magento\Framework\App\RequestInterface $request)
	{
		if (($alias = $this->getUrlAlias($request)) !== false) {
			if ($this->getBlogRoute()) {
				return strpos($alias . '/', $this->getBlogRoute() .'/') === 0 ? ltrim(substr($alias, strlen($this->getBlogRoute())), '/') : false;
			}
			
			return $alias;
		}
		
		return false;
	}
}
