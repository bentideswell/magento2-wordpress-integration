<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\App\Integration\Exception as IntegrationException;
use FishPig\WordPress\Model\Url as url;
use FishPig\WordPress\Model\Theme;
use FishPig\WordPress\Model\App\Path as WordPressPath;
use FishPig\WordPress\Model\App\WPConfig;
use FishPig\WordPress\Model\Network;

class App
{   
	/*
	 * @var bool
	 */
	protected $state = null;
	
	/*
	 * @var string|false
	 */
	protected $path = null;
	
	/*
	 * @var FishPig\WordPress\Model\App\Integration\Exception
	 */
	protected $exception = false;
	
	/*
	 * @var array
	 */
	protected $taxonomies = null;

	/*
	 * Array of the definitions from wp-config.php
	 *
	 * @var array|false
	 */
	protected $wpconfig = null;
	
	/*
	 * @var FishPig\WordPress\Model\App\Url
	 */
	protected $url;
	
	/*
	 * @var FishPig\WordPress\Model\Theme
	 */
	protected $themeHelper;

	/*
	 *
	 */
	protected $network;

	/*
	 *
	 */
	public function __construct(url $url, Theme $theme, WordPressPath $wpPath, WPConfig $wpConfig, Network $network)
	{
		$this->url     = $url;
		$this->theme   = $theme;
		$this->wpPath  = $wpPath;
		$this->network = $network;
	}
	
	/*
	 *
	 *
	 * @return void
	 */
	public function init()
	{
		return $this->_init();
	}
	
	/*
	 * Initialize the connection to WordPress
	 *
	 * @return $this
	 */
	protected function _init()
	{
		if (!is_null($this->state)) {
			return $this;
		}

		$this->state = false;

		try {
			$wpPath = $this->wpPath->getPath();
			// Check that the path to WordPress is valid
			if ($wpPath === false) {
				throw new \Exception('Unable to find a WordPress installation at specified path.');
			}
			
			// Check that the integration is successful
			$this->_validateIntegration();
			
			if ($this->theme->isThemeIntegrated()) {
				$this->theme->setPath($wpPath)->validate();
			}
			
			// Plugins can use this to check other things
			$this->performOtherChecks();

			// Mark the state as true. This means all is well
			$this->state = true;
		}
		catch (\Exception $e) {
			if (php_sapi_name() === 'cli') {
				echo $e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL . PHP_EOL;
				exit;
			}

			$this->exception = $e;
			$this->state = false;
		}
		
		return $this;
	}
	
	/*
	 * Check that the WP settings allow for integration
	 *
	 * @return bool
	 */
	protected function _validateIntegration()
	{
		if (!$this->theme->isThemeIntegrated()) {
			return $this;
		}

		$magentoUrl = $this->url->getMagentoUrl();

		if ($this->url->getHomeUrl() === $this->url->getSiteurl()) {
			IntegrationException::throwException(
				sprintf('Your WordPress Home URL matches your Site URL (%s).<br/>Your SiteURL should be the WordPress installation URL. The Home URL should be the integrated blog URL.', $this->url->getSiteurl())
			);
		}

		if ($this->isRoot()) {
			if ($this->url->getHomeUrl() !== $magentoUrl) {
				IntegrationException::throwException(
					sprintf('Your home URL is incorrect and should match your Magento URL. Change to. %s', $magentoUrl)
				);
			}
		}
		else {
			if (strpos($this->url->getHomeUrl(), $magentoUrl) !== 0) {
				IntegrationException::throwException(
					sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $this->url->getHomeUrl(), $magentoUrl)
				);
			}
			
			if ($this->url->getHomeUrl() === $magentoUrl) {
				IntegrationException::throwException('Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog');
			}
		}
		
		return $this;
	}
    
  /*
	 * Determine whether the integration is usable
	 *
	 * @return bool
	 */
  public function canRun()
  {
		$this->_init();
		
	  return $this->state === true;
  }
    
	/*
	 * Get the exception that occured during self::_init if it occured
	 *
	 * @return false|FishPig\WordPress\Model\App\Integration\Exception
	 */
	public function getException()
	{
		return $this->exception;
	}

	/*
	 *
	 *
	 * @return bool
	 */
	public function isRoot()
	{
		return false;
	}

	/*
	 * Can be implemented by plugins to carry out integration tests
	 *
	 * @return bool
	 */
	public function performOtherChecks()
	{
		return true;
	}
	
	/*
	 *
	 *
	 * @return false
	 */
	public function getCoreHelper()
	{
		return false;
	}
}
