<?php
/*
 * 
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use FishPig\WordPress\Model\OptionManager;
use Magento\Store\Model\StoreManagerInterface;

class Url
{
	/*
	 * @var OptionManager
	 */
	protected $optionManger;
	
	/*
	 * @var 
	 */
	protected $storeManager = null;
	 
	/*
	 * Constructor
	 */
	public function __construct(OptionManager $optionManager, StoreManagerInterface $storeManager)
	{
		$this->optionManager = $optionManager;
		$this->storeManager = $storeManager;
	}

	/*
	 * Get the Magento base URL
	 *
	 * @return string
	  */
	public function getMagentoUrl()
	{
		return rtrim(
			str_ireplace(
				'index.php',
				'',
				$this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_LINK)
			),
			'/'
		);
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
		return $this->getHomeUrl()	. '/' . $uri;
	}
	
	/*
	 * Get the Site URL
	 *
	 * @return string
	  */
	public function getSiteurl($extra = '')
	{
		$siteUrl = defined('FISHPIG_WP_SITEURL') ? FISHPIG_WP_SITEURL : $this->optionManager->getOption('siteurl');
		
		return rtrim(rtrim($siteUrl, '/') . '/' . ltrim($extra, '/'), '/');
	}
	
	/*
	 * Get the Home URL
	 *
	 * @return string
	  */
	public function getHomeUrl()
	{
		$home = defined('FISHPIG_WP_HOME') ? FISHPIG_WP_HOME : $this->optionManager->getOption('home');
		
		return rtrim($home, '/');
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
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getBaseFileUploadUrl()
	{
		return $this->getSiteUrl() . '/wp-content/uploads/';
	}
}
