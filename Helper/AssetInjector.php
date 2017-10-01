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
	 * @return
	 */
	public function __construct(App $app, StoreManagerInterface $storeManager, DirectoryList $directoryList, Filter $filter)
	{
		$this->app = $app->init();
		$this->storeManager = $storeManager;
		$this->directoryList = $directoryList;
		$this->filter = $filter;
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
			if ($buffer = $shortcodeInstance->getRequiredAssets()) {
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

			// Used to set correct tabs
			$level = 1;
			
			foreach($scripts as $skey => $script) {
				$tabs = str_repeat("  ", $level);
				
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
					$originalScriptUrl = $matches[1];
					
					$newScriptUrl = $this->_getRealJsUrl($originalScriptUrl); // Script might be rewritten
					$requireJsAlias = $this->_getRequireJsAlias($originalScriptUrl); // Alias lowercase basename of URL
					$requireJsPaths[$requireJsAlias] = $newScriptUrl; // Used to set paths
					
					$requireJsTemplate = str_replace(
						self::TMPL_TAG,
						$tabs . "require(['" . $requireJsAlias . "'], function() {\n" . $tabs . self::TMPL_TAG . "\n" . $tabs . "});" . "\n",
						$requireJsTemplate
					);
					
					$level++;
					
					$scripts[$skey] = str_replace($originalScriptUrl, $newScriptUrl, $script);
				}
				else {
					$requireJsTemplate = str_replace(self::TMPL_TAG, $this->_stripScriptTags($script) . "\n" . self::TMPL_TAG . "\n", $requireJsTemplate);
				}
			}

			// Remove final template variable placeholder
			$requireJsTemplate = str_replace(self::TMPL_TAG, '', $requireJsTemplate);
			
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
			$requireJsFinal = "<script type=\"text/javascript\">" . $requireJsConfig . $requireJsTemplate . "</script>";
			
			// Add the final requireJS code to the $content array
			$content .= $requireJsFinal;
		}
		
		// Fingers crossed and let's go!
		$bodyHtml = str_replace('</body>', $content . '</body>', $bodyHtml);
		
		return $bodyHtml;
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
		if (strpos($externalScriptUrl, $this->app->getWpUrlBuilder()->getSiteUrl()) !== false) {
			$localScriptFile = $this->app->getPath() . '/' . substr($externalScriptUrl, strlen($this->app->getWpUrlBuilder()->getSiteUrl()));
			$scriptContent = file_get_contents($localScriptFile);

			// Check whether the script supports AMD
			if (strpos($scriptContent, 'define.amd') !== false) {
				$newScriptFile = $this->directoryList->getPath('media') . $DS . 'js' . $DS . md5($externalScriptUrlFull) . '.js';
				$newScriptUrl = $this ->storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'js/' . basename($newScriptFile);
				
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
}
