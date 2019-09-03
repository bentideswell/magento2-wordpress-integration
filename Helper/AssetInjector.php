<?php
/*
 *
 *
 *
 */
namespace FishPig\WordPress\Helper;

/* Constructor Args */
use FishPig\WordPress\Model\IntegrationManager\Proxy as IntegrationManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Module\ModuleListInterface;
use FishPig\WordPress\Model\DirectoryList\Proxy as WPDirectoryList;
use FishPig\WordPress\Model\ShortcodeManager\Proxy as ShortcodeManager;
use FishPig\WordPress\Model\Url\Proxy as WordPressURL;

class AssetInjector
{
	/*
	 *
	 */
	const TMPL_TAG = '__FPTAG823434__';

	/*
	 * @var bool
	 */
	const DEBUG = false;

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
		   IntegrationManager $integrationManager, 
		StoreManagerInterface $storeManager, 
		        DirectoryList $directoryList, 
		  ModuleListInterface $moduleList,
		      WPDirectoryList $wpDirectoryList,
		     ShortcodeManager $shortcode,
		         WordPressURL $wpUrl
	)
	{
		$this->integrationManager = $integrationManager;
		$this->storeManager       = $storeManager;
		$this->directoryList      = $directoryList;
		$this->moduleVersion      = $moduleList->getOne('FishPig_WordPress')['setup_version'];
		$this->wpDirectoryList    = $wpDirectoryList;
		$this->shortcodeManager   = $shortcode;
		$this->wpUrl              = $wpUrl;
	}
	
	/*
	 * @return
	 */
	public function process($bodyHtml)
	{
		if (self::$status === true) {
			return false;
		}

		if (!defined('ABSPATH')) {
			return false;
		}

		$this->integrationManager->runTests();
		
		if ($this->isApiRequest() || $this->isAjaxRequest()) {
			return false;
		}

		if (!($shortcodes = $this->shortcodeManager->getShortcodesThatRequireAssets())) {
			return false;
		}
		
		self::$status = true;
		
		$assets = [];
		$inline = [];
		$metaLinks = [];
		
		// Get assets from plugins
		foreach($shortcodes as $class => $shortcodeInstance) {
			if (method_exists($shortcodeInstance, 'getRequiredAssets') && ($buffer = $shortcodeInstance->getRequiredAssets($bodyHtml))) {
				$assets = array_merge($assets, $buffer);
			}
		}

		// Get inline JS/CSS
		foreach($shortcodes as $class => $shortcodeInstance) {
			if (method_exists($shortcodeInstance, 'getInlineJs') && ($buffer = $shortcodeInstance->getInlineJs())) {
				$inline = array_merge($inline, $buffer);
			}
		}

		// Remove duplicate assets
		$assets = array_unique($assets);

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

		// If Visual Editor Mode, remove Magento JS and include WordPress JS without RequireJS
		$isVisualEditorMode = false;
		
		foreach($shortcodes as $class => $shortcodeInstance) {
			if (method_exists($shortcodeInstance, 'isVisualEditorMode') && ($buffer = $shortcodeInstance->isVisualEditorMode())) {
				$isVisualEditorMode = true;
				break;
			}
		}
		
		if ($isVisualEditorMode) {
			$bodyHtml = preg_replace('/<script[^>]*>.*<\/script>/Uis', '', $bodyHtml);
			$bodyHtml = str_replace('</body>', "\n\n" . $content . "\n\n" . '</body>', $bodyHtml);
			
			return $bodyHtml;
		}

		if (trim($content) === '') {
			return false;
		}

		// Get Head Meta and Link tags
		foreach($shortcodes as $class => $shortcodeInstance) {
			if (method_exists($shortcodeInstance, 'getMetaAndLinkTags') && ($buffer = $shortcodeInstance->getMetaAndLinkTags($bodyHtml))) {
				$metaLinks = array_merge($inline, $buffer);
			}
		}
    
    // Extract <link tags from content
    if (preg_match_all('/<link[^>]+>/', $content, $linkMatches)) {
      foreach($linkMatches[0] as $linkMatch) {
        $metaLinks[] = $linkMatch;
        $content = str_replace($linkMatch, '', $content);
      }
    }
    
    $content = trim($content);
    
    // IF we have any meta or link tags, add them into the head
    if ($metaLinks) {
      $bodyHtml = str_replace('</head>', implode("\n", $metaLinks) . "\n</head>", $bodyHtml);
    }
		
		// Now let's build the requireJS from $assets
		$baseUrl = $this->wpUrl->getSiteurl();
		$jsTemplate = '<script type="text/javascript" src="%s"></script>';
		$scripts    = [];
		$scriptsStatic  = [];
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
					if (in_array($match[2], ['text/template', 'text/x-template'])) {
						$scriptsStatic[] = $scripts[$skey];
						
						unset($scripts[$skey]);
						continue;
					}
					else if ($match[2] !== 'text/javascript') {
						$scriptsStatic[] = $scripts[$skey];
						
						unset($scripts[$skey]);
						continue;
					}
				}
				else if (preg_match('/<script[^>]+async/U', $script, $match)) {
					$scriptsStatic[] = $scripts[$skey];
					
					unset($scripts[$skey]);
					continue;
				}
				else if (preg_match('/<script([^>]*)>(.*)<\/script>/Us', $script, $match)){
					// Script tags with no SRC but data attributes
					if (trim($match[2]) === '') {
						if (strpos($match[1], ' src=') === false && strpos($match[1], ' data-') !== false) {
							$scriptsStatic[] = $scripts[$skey];
						
							unset($scripts[$skey]);
							continue;
						}
					}
				}
				
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
					$originalScriptUrl = $matches[1];
					
					// Ensure jQuery migrate isn't included multiple times
					if (strpos($originalScriptUrl, $this->getJqueryMigrateUrl()) === 0) {
						unset($scripts[$skey]);
						continue;
					}
					
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

					$migratedScriptUrl = $this->_migrateJsAndReturnUrl($realPathUrl);

					if (strpos($migratedScriptUrl, 'feefo') !== false) {
						// No .js					
						if (strpos($migratedScriptUrl, '.js') === false) {
							// No query string so lets add one to stop Magento adding .js
							if (strpos($migratedScriptUrl, '?') === false) {
								$migratedScriptUrl .= '?js=1';	
							}
						}
					}
										
					$scripts[$skey] = str_replace($originalScriptUrl, $migratedScriptUrl, $script);
				}
				else {
					$scripts[$skey] = $this->_fixDomReady($script);
				}
			}
    
      // After processing, no scripts so return
      if (count($scripts) > 0) {
  			if ($this->canMergeGroups()) {
  				$scripts = $this->_mergeGroups($scripts);
  			}
  
  			// Used to set paths for each JS file in requireJs
  			$requireJsPaths = ['jquery-migrate' => $this->getJqueryMigrateUrl()];
  			
  			// JS Template for requireJs. This changes through foreach below
  			$requireJsTemplate = "require(['jquery'], function(jQuery) {
  	require(['jquery-migrate', 'underscore'], function(jQueryMigrate, _) {
  		" . self::TMPL_TAG . "
  });				
  });";
  
  			$level = 2;
  			
  			foreach($scripts as $skey => $script) {
  				$tabs = str_repeat("	", $level);
  
  				if (!preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
            $inlineJsExternalFile = $this->getBaseJsPath() . 'inex-' . md5($script) . '.js';
            $inlineJsExternalFileMin = substr($inlineJsExternalFile, 0, -3) . '.min.js';
            
            // Remove the wrapping script tags
            $script = trim(preg_replace('/<[\/]{0,1}script[^>]*>/', '', $script));
            
            // Ensure that the static asset directory exists
            $this->createStaticAssetDirectory();
            
            // Save the JS in the external file
            file_put_contents($inlineJsExternalFile, $script);
            file_put_contents($inlineJsExternalFileMin, $script);
            
            $inlineJsExternalUrl = $this->getBaseJsUrl() . basename($inlineJsExternalFile);
  
            $scripts[$skey] = $script = '<script type="text/javascript" src="' . $inlineJsExternalUrl . '"></script>';
          }				
          
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
  			$requireJsTemplate = str_replace(self::TMPL_TAG, 'FPJS.trigger();', $requireJsTemplate);
  
  			// Start of paths template
  			$requireJsConfig = "requirejs.config({\n  \"paths\": {\n    ";
  
  			// Loop through paths, remove .js and set
  			foreach($requireJsPaths as $alias => $path) {
  				if (substr($path, -3) === '.js') {
  					$path = substr($path, 0, -3);
  				}
  				
  				if (strpos($path, '&#')) {
  					$path = html_entity_decode($path);
  				}
  
  				$requireJsConfig .= '"' . $alias . '": "' . $path . '",' . "\n    ";
  			}
  				
  			$requireJsConfig = rtrim($requireJsConfig, "\n ,") . "\n  }\n" . '});';
  			
  			// Final JS including wrapping script tag
  			$requireJsFinal = "<script type=\"text/javascript\">" . "\n\n" . $this->getFPJS() . "\n\n" . $requireJsConfig . "\n\n" . $requireJsTemplate . "</script>";
  
  			// Add the final requireJS code to the $content array
  			$content .= $requireJsFinal;
  		}
		}

		// Add in the JS templates
		if ($scriptsStatic) {
			$content = implode(PHP_EOL, $scriptsStatic) . $content;
		}
		
    if ($content) {
      $bodyHtml = str_replace('</body>', trim($content) . "\n" . '</body>', $bodyHtml);
    }
		
		return $bodyHtml;
	}

	/*
	 * Get the FPJS object code
	 *
	 * @return string
	 */
	protected function getFPJS()
	{
		return 'FPJS=new(function(){this.fs=[];this.s=false;this.on=function(a,b){if(this.s){b();}else{this.fs.push(b);}};this.trigger=function(){this.s=!0;for(var i in this.fs){this.fs[i](jQuery);}this.fs=[];}})();';
	}
	
	/*
	 *
	 * @param string $url
	 * @return string
	 */
	protected function _getRequireJsAlias($url)
	{
		$alias = basename($url);

		if (strpos($alias, '?') !== false) {
			$alias = substr($alias, 0, strpos($alias, '?'));
		}

		$requireJsAlias = str_replace('.', '_', basename(basename($alias, '.js'), '.min'));

		if ($requireJsAlias && strlen($requireJsAlias) > 5) {
			return $requireJsAlias;
		}					

		return $this->_hashString($url);
	}
	
	/*
	 * Given a URL, check for define.AMD and if found, rewrite file and disable this functionality
	 *
	 * @param string $externalScriptUrlFull
	 * @return string
	 */
	protected function _migrateJsAndReturnUrl($externalScriptUrlFull)
	{
		// Check that the script is a local file
		if (!$this->_isWordPressUrl($externalScriptUrlFull)) {
			return $externalScriptUrlFull;
		}

		$externalScriptUrl = $this->_cleanQueryString($externalScriptUrlFull);		
		$localScriptFile 	 = $this->wpDirectoryList->getBasePath() . '/' . ltrim(substr($externalScriptUrl, strlen($this->wpUrl->getSiteUrl())), '/');
		$newScriptFile	 	 = $this->getBaseJsPath() . $this->_hashString($externalScriptUrlFull) . '.js';
		$newScriptUrl 		 = $this->getBaseJsUrl() . basename($newScriptFile);

		if (!self::DEBUG && is_file($newScriptFile) && filemtime($localScriptFile) <= filemtime($newScriptFile)) {
			/* Debug */
#			return preg_replace('/\.js$/', '', preg_replace('/\?.*$/', '', $externalScriptUrlFull));
			return $newScriptUrl;
		}
			
		$scriptContent = file_get_contents(urldecode($localScriptFile));
		$scriptContent = $this->_fixDomReady($scriptContent);

		// Check whether the script supports AMD
		if (strpos($scriptContent, 'define.amd') !== false) {
			$scriptContent = "__d=define;define=undefined;" . rtrim($scriptContent, ';') . ";define=__d;__d=undefined;";
		}

		if (self::DEBUG) {
			$debugFilename = basename($newScriptFile, '.js') . '-' . trim(preg_replace('/[^a-z0-9_\-\.]{1,}/', '-', str_replace(array('.js', $this->wpDirectoryList->getBasePath()), '', $localScriptFile)), '-') . '.js';
			$debugFilename = preg_replace('/[_-]{1,}/', '-', $debugFilename);
			$newScriptFile = dirname($newScriptFile) . DIRECTORY_SEPARATOR . $debugFilename;
			$newScriptUrl = dirname($newScriptUrl) . '/' . $debugFilename;
			$scriptContent = '/* ' . $externalScriptUrlFull . ' */' . PHP_EOL . $scriptContent;
		}

    // Ensure that the static asset directory exists
    $this->createStaticAssetDirectory();

		// Only write data if new script doesn't exist or local file has been updated
		file_put_contents($newScriptFile, $scriptContent);
		file_put_contents(dirname($newScriptFile) . DIRECTORY_SEPARATOR . basename($newScriptFile, '.js') . '.min.js', $scriptContent);
		
		return $newScriptUrl;
	}

	/*
	 * Fix DOM Ready calls
	 *
	 * @param  string $scriptContent
	 * @return string
	 */
	protected function _fixDomReady($scriptContent)
	{
		$scriptContent = preg_replace('/[a-zA-Z$]{1,}\(document\)\.ready\(/', 'FPJS.on(\'fishpig_ready\', ', $scriptContent);			
		$scriptContent = preg_replace('/jQuery\([\s]{0,}function\(/i', 'FPJS.on(\'fishpig_ready\', function(', $scriptContent);

		return $scriptContent;
	}
	
	/*
	 * Given a URL, check for define.AMD and if found, rewrite file and disable this functionality
	 *
	 * @param string $externalScriptUrlFull
	 * @return string
	 */
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
				$localScriptFile = $this->wpDirectoryList->getBasePath() . '/' . substr($externalScriptUrl, strlen($this->wpUrl->getSiteUrl()));
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
		return strpos($this->_cleanQueryString($url), $this->wpUrl->getSiteUrl()) === 0;
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

	/*
	 *
	 * @param string $s
	 * @return string
	 */
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
	
	/**
   * @return string
   */
  public function getJqueryMigrateUrl()
  {
    return $this->wpUrl->getSiteUrl() . '/wp-includes/js/jquery/jquery-migrate.min.js';
  }

	/**
   * @return string
   */
  protected function getBaseJsUrl()
  {
    return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_STATIC) . 'frontend/FishPig/WordPress/js/';
  }

	/**
   * @return string
   */
  protected function getBaseJsPath()
  {
    return $this->directoryList->getPath('static') . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR . 'FishPig' . DIRECTORY_SEPARATOR . 'WordPress' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR;
  }

  /**
   *
   */
  protected function createStaticAssetDirectory()
  {
    $assetDir = $this->getBaseJsPath();
    
    if (!is_dir($assetDir)) {
      return @mkdir($assetDir, 0777, true) && is_dir($assetDir);
    }

    return true;
  }
}
