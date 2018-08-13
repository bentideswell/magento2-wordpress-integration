<?php
/*
 *
 */
namespace FishPig\WordPress\Model\App;

use FishPig\WordPress\Model\Config;

class Path
{   
	protected $path;
	
	public function __construct(Config $config)
	{
		$this->config = $config;
	}
	
  public function getPath()
  {
		if (!is_null($this->path)) {
			return $this->path;
		}
		
		$this->path = false;
		
		if (!($path = trim($this->config->getStoreConfigValue('wordpress/setup/path')))) {
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
