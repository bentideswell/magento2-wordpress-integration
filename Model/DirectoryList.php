<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use FishPig\WordPress\Model\WPConfig\Proxy as WPConfig;

/* Misc */
use Exception;
use Magento\Store\Model\ScopeInterface;

class DirectoryList
{   
	/*
	 *
	 */
	protected $scopeConfig;
	
	/*
	 *
	 */
	protected $storeManager;
	
	/*
	 *
	 */
	protected $wpConfig;

	/*
	 *
	 */
	protected $basePath = [];

	/*
	 *
	 */
	public function __construct(StoreManagerInterface $storeManager, ScopeConfigInterface $scopeConfig, WPConfig $wpConfig)
	{
		$this->scopeConfig  = $scopeConfig;
		$this->storeManager = $storeManager;
		$this->wpConfig     = $wpConfig;
	}
	
	/*
	 *
	 * @return string
	 */
  public function getBasePath()
  {
	  $storeId = $this->getStoreId();

	  if (!isset($this->basePath[$storeId])) {
		  $this->basePath[$storeId] = false;

			if (!($path = trim($this->scopeConfig->getValue('wordpress/setup/path', ScopeInterface::SCOPE_STORE, $storeId)))) {
				// Might not be right but worth a shot!
				$path = 'wp';
			}

			if (substr($path, 0, 1) !== '/') {
        if (is_dir(BP . '/pub/' . $path)) {
					$path = BP . '/pub/' . $path;
				}
				else if (is_dir(BP . '/' . $path)) {
					$path = BP . '/' . $path;
				}
			}
			
			if (!is_dir($path) || !is_file($path . '/wp-config.php')) {
				return false;
			}
			
			$this->basePath[$storeId] = $path;
		}
		
		return $this->basePath[$storeId];
  }	

	/*
	 *
	 *
	 * @return bool
	 */
	public function isValidBasePath()
	{
		return $this->getBasePath() !== false;
	}

	/*
	 *
	 *
	 * @return string
	 */	
	public function getWpContentDir()
	{
		if (!($contentDir = $this->wpConfig->getData('WP_CONTENT_DIR'))) {
			$contentDir = $this->getBasePath() . '/wp-content';
		}
		
		return rtrim($contentDir, '/');
	}
	
	/*
	 *
	 *
	 * @return string
	 */	
	public function getThemeDir()
	{
		return $this->getWpContentDir() . '/themes';
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
