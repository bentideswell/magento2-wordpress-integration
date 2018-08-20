<?php
/*
 *
 */
namespace FishPig\WordPress\Model\App;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Path
{   
	/*
	 *
	 */
	protected $scopeConfig;

	/*
	 *
	 */
	protected $path;

	/*
	 *
	 */
	public function __construct(ScopeConfigInterface $scopeConfig)
	{
		$this->scopeConfig = $scopeConfig;
	}
	
	/*
	 *
	 * @return string
	 */
  public function getPath()
  {
		if (!is_null($this->path)) {
			return $this->path;
		}
		
		$this->path = false;
		
		if (!($path = trim($this->scopeConfig->getValue('wordpress/setup/path')))) {
			return $this->path;
		}
		
		if (substr($path, 0, 1) !== '/') {
			if (is_dir(BP . '/' . $path)) {
				$path = BP . '/' . $path;
			}
			else if (is_dir(BP . '/pub/' . $path)) {
				$path = BP . '/pub/' . $path;
			}
		}
		
		if (!is_dir($path) || !is_file($path . '/wp-config.php')) {
			return $this->path;
		}

		return $this->path = $path;
  }	
}
