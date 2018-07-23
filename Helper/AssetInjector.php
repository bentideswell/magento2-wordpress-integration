<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Helper;

use \FishPig\WordPress\Model\App;
use \Magento\Framework\Registry;
use \Magento\Framework\Event\ObserverInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Module\ModuleListInterface;
use \FishPig\WordPress\Helper\Filter;

class AssetInjector
{
	/*
	 *
	 */
	const TMPL_TAG = '__FPTAG823434__';

	/*
	 * Status determines whether already ran
	 *
	 * @var bool
	 */
	static protected $status = false;
	
	/*
	 * Module version. This is used for generating md5 hashes.
	 *
	 * @var string
	 */
	protected $moduleVersion;
	
	/*
	 * @return
	 */
	public function __construct(
		App $app, 
		StoreManagerInterface $storeManager, 
		DirectoryList $directoryList, 
		Filter $filter, 
		ModuleListInterface $moduleList
	)
	{
		$this->app = $app->init();
		$this->storeManager = $storeManager;
		$this->directoryList = $directoryList;
		$this->filter = $filter;
		$this->moduleVersion = $moduleList->getOne('FishPig_WordPress')['setup_version'];
	}
	
	/*
	 * @return
	 */
	public function process($bodyHtml)
	{
		if (self::$status === true) {
			return false;
		}
		
		if (!$this->app->canRun() || $this->isApiRequest() || $this->isAjaxRequest()) {
			return false;
		}
		
		if (!($shortcodes = $this->filter->getAssetInjectionShortcodes())) {
			return false;
		}

		self::$status = true;
		
		$assets = [];
		$inline = [];
		
		// Get assets from plugins
		foreach($shortcodes as $class => $shortcodeInstance) {
			if ($buffer = $shortcodeInstance->getRequiredAssets($bodyHtml)) {
				$assets = array_merge($assets, $buffer);
			}
		}

		// Get inline JS/CSS
		foreach($shortcodes as $class => $shortcodeInstance) {
			if ($buffer = $shortcodeInstance->getInlineJs()) {
				$inline = array_merge($inline, $buffer);
			}
		}

		// Remove any JS/CSS that is in $inline from $assets to prevent duplication
		if (count($inline) > 0) {
			foreach($inline as $asset) {
				if (($key = array_search($asset, $assets)) !== false) {
					unset($assets[$key]);
				}
			}
		}
		
		// Merge inline into assets
		$assets = array_merge($assets, $inline);

		if (count($assets) === 0) {
			return false;
		}

		$content = implode("\n", $assets);

		if (trim($content) === '') {
			return false;
		}

		// Now let's build the requireJS from $assets
		$baseUrl = $this->app->getWpUrlBuilder()->getSiteurl();
		$jsTemplate = '<script type="text/javascript" src="%s"></script>';
		$scripts = array();
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

		if (count($scripts) > 0) {
			/*
			 * Migrate JS to Magento
			 * Add define if required
			 * Modify jQuery document ready events
			 */
			foreach($scripts as $skey => $script) {
				if (preg_match('/type=(["\']{1})(.*)\\1/U', $script, $match)) {
					if ($match[2] !== 'text/javascript') {
						unset($scripts[$skey]);
						continue;
					}
				}
				
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
					$originalScriptUrl = $matches[1];
					
					// This is needed to fix ../ in URLs
					$realPathUrl = $originalScriptUrl;
					
					if (strpos($originalScriptUrl, '../') !== false) {
						$urlParts = explode('/', $originalScriptUrl);
						
						while(($key = array_search('..', $urlParts)) !== false) {
							if (!isset($urlParts[$key-1])) {
								break;
							}

							unset($urlParts[$key-1]);
							unset($urlParts[$key]);
						}
						
						$realPathUrl = implode('/', $urlParts);
					}

					$scripts[$skey] = str_replace($originalScriptUrl, $this->_migrateJsAndReturnUrl($realPathUrl), $script);
				}
			}

			if ($this->canMergeGroups()) {
				$scripts = $this->_mergeGroups($scripts);
			}

			// Used to set paths for each JS file in requireJs
			$requireJsPaths = array(
				'jquery-migrate' => $this->app->getWpUrlBuilder()->getSiteUrl() . '/wp-includes/js/jquery/jquery-migrate.min.js',
			);
			
			// JS Template for requireJs. This changes through foreach below
			$requireJsTemplate = "require(['jquery'], function(jQuery) {
	require(['jquery-migrate', 'underscore'], function(jQueryMigrate, _) {
		" . self::TMPL_TAG . "
});				
});";

			$level = 2;
			
			foreach($scripts as $skey => $script) {
				$tabs = str_repeat("	", $level);
				
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
					$originalScriptUrl = $matches[1];
					
					$requireJsAlias = $this->_getRequireJsAlias($originalScriptUrl); // Alias lowercase basename of URL
					$requireJsPaths[$requireJsAlias] = $originalScriptUrl; // Used to set paths
					
					$requireJsTemplate = str_replace(
						self::TMPL_TAG,
						$tabs . "require(['" . $requireJsAlias . "'], function() {\n" . $tabs . self::TMPL_TAG . $tabs . "});" . "\n",
						$requireJsTemplate
					);
					
					$level++;
				}
				else {
					$requireJsTemplate = str_replace(self::TMPL_TAG, $this->_stripScriptTags($script) . "\n" . self::TMPL_TAG . "\n", $requireJsTemplate);
				}
			}

			// Remove final template variable placeholder
			$requireJsTemplate = str_replace(self::TMPL_TAG, 'jQuery(document).trigger(\'fishpig_ready\');', $requireJsTemplate);
			
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
			$requireJsFinal = "<script type=\"text/javascript\">" . "\n\n" . $requireJsConfig . "\n\n" . $requireJsTemplate . "</script>";
			
			// Add the final requireJS code to the $content array
			$content .= $requireJsFinal;
		}

		// Fingers crossed and let's go!
		$bodyHtml = str_replace('</body>', $content . '</body>', $bodyHtml);
		
		return $bodyHtml;
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

		$requireJsAlias = str_replace('.', '_', basename(basename($alias, '.js'), '.min'));
		
		if ($requireJsAlias) {
			return $requireJsAlias;
		}					

		return $this->_hashString($url);
	}
	
	/**
	 * Given a URL, check for define.AMD and if found, rewrite file and disable this functionality
	 *
	 * @param string $externalScriptUrlFull
	 * @return string
	**/
	protected function _migrateJsAndReturnUrl($externalScriptUrlFull)
	{
		// Check that the script is a local file
		if (!$this->_isWordPressUrl($externalScriptUrlFull)) {
			return $externalScriptUrlFull;
		}

		$forceRefresh			 = false;
		$externalScriptUrl = $this->_cleanQueryString($externalScriptUrlFull);
		$localScriptFile 	 = $this->app->getPath() . '/' . substr($externalScriptUrl, strlen($this->app->getWpUrlBuilder()->getSiteUrl()));
		$newScriptFile	 	 = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . $this->_hashString($externalScriptUrlFull) . '.js';
		$newScriptUrl 		 = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'js/' . basename($newScriptFile);

		if (!$forceRefresh && is_file($newScriptFile) && filemtime($localScriptFile) <= filemtime($newScriptFile)) {
			/* Debug */
#			return preg_replace('/\.js$/', '', preg_replace('/\?.*$/', '', $externalScriptUrlFull));
			return $newScriptUrl;
		}
			
		$scriptContent = file_get_contents($localScriptFile);

		$docReady = '(document).ready(';
		
		if (stripos($scriptContent, $docReady) !== false) {			
			$scriptContent = preg_replace('/[a-zA-Z$]{1,}\(document\)\.ready\(/', 'jQuery(document).on(\'fishpig_ready\', {}, ', $scriptContent);			
#			$scriptContent = str_replace($docReady, 'jQuery(document).on(\'fishpig_ready\', {}, ', $scriptContent);
		}

		// Check whether the script supports AMD
		if (strpos($scriptContent, 'define.amd') !== false) {
			$scriptContent = "__d=define;define=null;\ntry{\n" . $scriptContent . "}catch (e){console.error&&console.error(e.message);}\ndefine=__d;__d=null;";
		}

		@mkdir(dirname($newScriptFile));
			
		// Only write data if new script doesn't exist or local file has been updated
		file_put_contents($newScriptFile, $scriptContent);
		file_put_contents(dirname($newScriptFile) . DIRECTORY_SEPARATOR . basename($newScriptFile, '.js') . '.min.js', $scriptContent);
		
		return $newScriptUrl;
	}

	/**
	 * Given a URL, check for define.AMD and if found, rewrite file and disable this functionality
	 *
	 * @param string $externalScriptUrlFull
	 * @return string
	**/
	protected function _getMergedJsUrl(array $externalScriptUrlFulls)
	{
		$DS = DIRECTORY_SEPARATOR;
		$baseMergedPath = $this->directoryList->getPath('media') . $DS . 'js' . $DS;
		$scriptContents = array();
		
		foreach($externalScriptUrlFulls as $externalScriptUrlFull) {
			$externalScriptUrl = $this->_cleanQueryString($externalScriptUrlFull);
			
			if ($this->_isMigratedUrl($externalScriptUrl)) {
				$localScriptFile = $baseMergedPath . basename($externalScriptUrl);
			}
			else {
				$localScriptFile = $this->app->getPath() . '/' . substr($externalScriptUrl, strlen($this->app->getWpUrlBuilder()->getSiteUrl()));
			}
			
			$scriptContents[] = file_get_contents($localScriptFile);
		}
		
		$scriptContent = implode("\n\n", $scriptContents);
		$newScriptFile = $baseMergedPath . $this->_hashString(implode('-', $externalScriptUrlFulls) . rand(1, 99999)) . '.js';
		$newScriptUrl = $this ->storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'js/' . basename($newScriptFile);
				
		@mkdir(dirname($newScriptFile));
				
		// Only write data if new script doesn't exist or local file has been updated
		if (!is_file($newScriptFile) || filemtime($localScriptFile) > filemtime($newScriptFile)) {
			file_put_contents($newScriptFile, $scriptContent);
		}

		return $newScriptUrl;
	}
	
	/*
	 * Determine whether the request is an API request
	 *
	 * @return bool
	 */
	public function isApiRequest()
	{
		$pathInfo = str_replace(
			$this->storeManager->getStore()->getBaseUrl(), 
			'', 
			$this->storeManager->getStore()->getCurrentUrl()
		);

		return strpos($pathInfo, 'api/') === 0;
	}
	
	/*
	 * Determine whether the current request is an ajax request
	 *
	 * @return bool
	 */
	public function isAjaxRequest()
	{
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}

	/*
	 * Determine whether the URL is a WordPress URL
	 *
	 * @param string $url
	 * @return bool
	 */
	protected function _isWordPressUrl($url)
	{
		return strpos($this->_cleanQueryString($url), $this->app->getWpUrlBuilder()->getSiteUrl()) === 0;
	}

	/*
	 * Determine whether the URL is a JS URL from WordPress that has been migrated into Magento
	 *
	 * @param string $url
	 * @return bool
	 */
	protected function _isMigratedUrl($url)
	{
		return strpos($this->_cleanQueryString($url), $this->storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'js/') === 0;		
	}
	
	/*
	 * Clean the query string from the url
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _cleanQueryString($url)
	{
		return strpos($url, '?') === false ? $url : substr($url, 0, strpos($url, '?'));
	}

	/**
	 *
	 * @param string $s
	 * @return string
	**/
	protected function _stripScriptTags($s)
	{
		return preg_replace(
			'/<\/script>$/',
			'',
			preg_replace(
				'/^<script[^>]{0,}>/',
				'',
				trim($s)
			)
		);
	}

	/*
	 * Determine whether to merge groups
	 * This is currently disabled
	 *
	 * @return bool
	 */
	public function canMergeGroups()
	{
		return false;
	}

	/*
	 * Merge JS files where possible
	 *
	 * @param array $scripts
	 * @return array
	 */
	protected function _mergeGroups($scripts)
	{
		$buffer = array();
		$bkey = 1;
		
		// Create $buffer for merged groups
		foreach($scripts as $skey => $script) {
			if (preg_match('/<script[^>]+src=[\'"]{1}(.*)[\'"]{1}/U', $script, $smatch)) {
				if ($this->_isWordPressUrl($smatch[1]) || $this->_isMigratedUrl($smatch[1])) {
					$buffer[$bkey][] = $smatch[1];
					continue;
				}
			}

			$bkey++;
			$buffer[$bkey] = $script;
			$bkey++;
		}

		$scripts = $buffer;

		// Merge groups
		foreach($scripts as $skey => $script) {
			if (is_array($script)) {
				$scripts[$skey] = '<script type="text/javascript" src="' . $this->_getMergedJsUrl($script) . '"></script>';
			}
		}
		
		return $scripts;
	} 
	
	/*
	 * Hash a string (filename) with a version/salt
	 *
	 * @param  string
	 * @return string
	 */
	protected function _hashString($s)
	{
		return md5($this->moduleVersion . $s);
	}
}
