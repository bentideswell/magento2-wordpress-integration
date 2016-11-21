<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Helper;

use Magento\Framework\App\Helper\Context;
use FishPig\WordPress\Model\Config;
use FishPig\WordPress\Model\App\Integration\Exception as IntegrationException;
use Magento\Framework\Module\Dir\Reader as ModuleDirReader;

class Theme extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 * @var
	**/
	const THEME_NAME = 'fishpig';
	
	/**
	 * @var
	**/
	const DS = DIRECTORY_SEPARATOR;
	
	/**
	 * @var
	**/
	protected $_path = '';
	
	/**
	 * @var
	**/
	protected $_config = null;
	
	/**
	 * @var
	**/
	protected $_autoInstall = true;
	
	/**
	 * @
	**/
    public function __construct(Context $context, Config $config, ModuleDirReader $moduleDirReader)
    {
		parent::__construct($context);
		
	    $this->_config = $config;
	    $this->_moduleDirReader = $moduleDirReader;
    }

	/**
	 * @
	**/
	public function setPath($path)
	{
		$this->_path = $path;
		
		return $this;
	}

	/**
	 * @
	**/
	public function validate()
	{
		$ds = DIRECTORY_SEPARATOR;
		
		if (!$this->_path || !is_dir($this->_path)) {
			IntegrationException::throwException(
				'Empty or invalid path set.'
			);
		}
		
		$targetDir = $this->getTargetDir();

		if (!is_dir($targetDir)) {
			if (!$this->canAutoInstallTheme()) {
				IntegrationException::throwException(
					'The FishPig WordPress theme is not installed. <a href="?install-theme=1">Click here to install it</a>.'
				);
			}
			
			@mkdir($targetDir, 0777, true);
			
			if (!is_dir($targetDir)) {
				IntegrationException::throwException(
					'The FishPig WordPress theme is not installed and due to the permissions of the WordPress theme folder, it cannot be installed automatically. Please copy the contents of app/code/FishPig/WordPress/wptheme to the wp-content/themes/fishpig folder.'
				);
			}
		}
		
		$sourceDir = $this->_moduleDirReader->getModuleDir('', 'FishPig_WordPress') . $ds . 'wptheme';
		$sourceFiles = scandir($sourceDir);
		
		foreach($sourceFiles as $sourceFile) {
			if (trim($sourceFile, '.') === '') {
				continue;
			}
			
			$targetFile = $targetDir . self::DS . $sourceFile;
			$sourceFile = $sourceDir . self::DS . $sourceFile;
			
			if (!$this->isFileWriteable($targetFile)) {
				IntegrationException::throwException(
					'Unable to install a WordPress theme file due to permissions. File is ' . $targetFile
				);
			}
			
			$sourceData = file_get_contents($sourceFile);
			$targetData = file_exists($targetFile) ? file_get_contents($targetFile) : '';
			
			if ($sourceData !== $targetData) {
				file_put_contents($targetFile, $sourceData);
			}
		}
		
		if (!$this->isActive()) {
			IntegrationException::throwException(
				'The FishPig WordPress theme is installed but is not active. Please login to the WordPress Admin and enable it.'
			);
		}
		
		return $this;
	}
	
	/**
	 * @
	**/
	public function isFileWriteable($file)
	{
		return is_file($file) && is_writeable($file) || !is_file($file) && is_writable(dirname($file));
	}
	
	/**
	 * @
	**/
	public function isActive()
	{
		return $this->_config->getOption('template') === self::THEME_NAME
			&& $this->_config->getOption('stylesheet') === self::THEME_NAME;
	}
	
	/**
	 * @
	**/
	public function getTargetDir()
	{
		return $this->_path . self::DS . 'wp-content' . self::DS . 'themes' . self::DS . self::THEME_NAME;
	}
	
	/**
	 * @
	**/
	public function getSourceDir()
	{
		return $this->_moduleDirReader->getModuleDir('', 'FishPig_WordPress') . self::DS . 'wptheme';
	}
	
	/**
	 * @
	**/
	public function canAutoInstallTheme()
	{
		return (int)$this->_request->getParam('install-theme') === 1 || $this->_autoInstall === true;
	}
}
