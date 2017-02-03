<?php
/**
 * @category Fishpig
 * @package Fishpig_Wordpress
 * @license http://fishpig.co.uk/license.txt
 * @author Ben Tideswell <ben@fishpig.co.uk>
 */
namespace FishPig\WordPress\Observer;

use \FishPig\WordPress\Model\App;
use \Magento\Framework\Registry;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;

class InjectContent implements ObserverInterface
{
	/**
	  * @return
	 **/
	public function __construct(App $app, StoreManagerInterface $storeManager, DirectoryList $directoryList)
	{
		$this->_app = $app->init();
		$this->_storeManager = $storeManager;
		$this->directoryList = $directoryList;
	}
	
	/**
	  * @return
	 **/
	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		if (!$this->_app->canRun()) {
			return $this;
		}

		if ($this->isApiRequest()) {
			return $this;
		}
		
		$content = $this->getHeadFooterContent();
		
		if (count($content) > 0) {
			$bodyHtml = $observer->getEvent()
					->getResponse()
						->getBody();
	
			$baseUrl = $this->_app->getWpUrlBuilder()->getSiteurl();
			$jsTemplate = '<script type="text/javascript" src="%s"></script>';

			$content = implode("\n", $content);
			
			$scriptRegex = '<script.*<\/script>';
			$regexes = array(
				'<!--\[[a-zA-Z0-9 ]{1,}\]>[\s]{0,}' . $scriptRegex . '[\s]{0,}<!\[endif\]-->',
				$scriptRegex
			);
		
			// Extract all JS from $content
			foreach($regexes as $regex) {
				if (preg_match_all('/' . $regex . '/sUi', $content, $matches)) {
					foreach($matches[0] as $v) {
						$content = str_replace($v, '', $content);
						$scripts[] = $v;
					}
				}
			}

			// Used to set paths for each JS file in requireJs
			$requireJsPaths = array(
				'jquery-migrate' => $this->_app->getWpUrlBuilder()->getSiteUrl() . '/wp-includes/js/jquery/jquery-migrate.min.js',
			);
			
			// JS Template for requireJs. This changes through foreach below
			$requireJsTemplate = "require(['jquery', 'jquery-migrate', 'underscore'], function(jQuery, jQueryMigrate, _) {\n  %s\n});";
			
			// Used to set correct tabs
			$level = 1;
			
			foreach($scripts as $skey => $script) {
				$tabs = str_repeat("  ", $level);
				
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
					$originalScriptUrl = $matches[1];
					
					$newScriptUrl = $this->_getRealJsUrl($originalScriptUrl); // Script might be rewritten
					$requireJsAlias = $this->_getRequireJsAlias($originalScriptUrl); // Alias lowercase basename of URL
					$requireJsPaths[$requireJsAlias] = $newScriptUrl; // Used to set paths
					
					$requireJsTemplate = sprintf($requireJsTemplate, $tabs. "require(['" . $requireJsAlias . "'], function() {\n" . $tabs . "%s\n" . $tabs . "});" . "\n");
					$level++;
					
					$scripts[$skey] = str_replace($originalScriptUrl, $newScriptUrl, $script);
				}
				else {
					// Inline JS
					$requireJsTemplate = sprintf($requireJsTemplate, strip_tags($script) . "\n%s\n");
				}
			}

			// Remove final template variable placeholder
			$requireJsTemplate = str_replace('%s', '', $requireJsTemplate);
			
			// Start of paths template
			$requireJsConfig = "requirejs.config({\n  \"paths\": {\n    ";
				
			// Loop through paths, remove .js and set
			foreach($requireJsPaths as $alias => $path) {
				if (substr($path, -3) === '.js') {
					$path = substr($path, 0, -3);
				}

				$requireJsConfig .= '"' . $alias . '": "' . $path . '",' . "\n    ";
			}
				
			$requireJsConfig = rtrim($requireJsConfig, "\n ,") . "\n  }\n" . '});';
			
			// Final JS including wrapping script tag
			$requireJsFinal = sprintf("<script type=\"text/javascript\">" . $requireJsConfig . "%s</script>", $requireJsTemplate);

			// Fingers crossed and let's go!
			$observer->getEvent()->getResponse()->setBody(str_replace('</body>', $content . $requireJsFinal . '</body>', $bodyHtml));
		}
		
		return $this;
	}
	
	/**
	 *
	 * @param string $url
	 * @return string
	**/
	protected function _getRequireJsAlias($url)
	{
		$alias = basename($url);
		
		if (strpos($alias, '?') !== false) {
			$alias = substr($alias, 0, strpos($alias, '?'));
		}
			
		return str_replace('.', '_', basename(basename($alias, '.js'), '.min'));
	}
	
	/**
	 * Given a URL, check for define.AMD and if found, rewrite file and disable this functionality
	 *
	 * @param string $externalScriptUrlFull
	 * @return string
	**/
	protected function _getRealJsUrl($externalScriptUrlFull)
	{
		$DS = DIRECTORY_SEPARATOR;
		$externalScriptUrl = strpos($externalScriptUrlFull, '?') === false 
			? $externalScriptUrlFull 
			: substr($externalScriptUrlFull, 0, strpos($externalScriptUrlFull, '?'));

		// Check that the script is a local file
		if (strpos($externalScriptUrl, $this->_app->getWpUrlBuilder()->getSiteUrl()) !== false) {
			$localScriptFile = $this->_app->getPath() . '/' . substr($externalScriptUrl, strlen($this->_app->getWpUrlBuilder()->getSiteUrl()));
			$scriptContent = file_get_contents($localScriptFile);

			// Check whether the script supports AMD
			if (strpos($scriptContent, 'define.amd') !== false) {
				$newScriptFile = $this->directoryList->getPath('media') . $DS . 'css' . $DS . md5($externalScriptUrlFull) . '.js';
				$newScriptUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'css/' . basename($newScriptFile);
				
				@mkdir(dirname($newScriptFile));
				
				// Only write data if new script doesn't exist or local file has been updated
				if (!is_file($newScriptFile) || filemtime($localScriptFile) > filemtime($newScriptFile)) {
					file_put_contents(
						$newScriptFile, 
						"__d=define;define=null;\n" . $scriptContent . "\ndefine=__d;__d=null;"
					);
				}
				
				return $newScriptUrl;
			}
		}
		
		return $externalScriptUrl;
	}
	
	/**
	 * Determine whether the request is an API request
	 *
	 * @return bool
	**/
	public function isApiRequest()
	{
		$pathInfo = str_replace(
			$this->_storeManager->getStore()->getBaseUrl(), 
			'', 
			$this->_storeManager->getStore()->getCurrentUrl()
		);

		return strpos($pathInfo, 'api/') === 0;
	}
	
	/**
	  * @return
	 **/
	public function getHeadFooterContent()
	{
		return array();
	}
}
