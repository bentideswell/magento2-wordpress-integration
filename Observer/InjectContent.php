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

class InjectContent implements ObserverInterface
{
	/**
	  * @return
	 **/
	public function __construct(App $app, StoreManagerInterface $storeManager)
	{
		$this->_app = $app->init();
		$this->_storeManager = $storeManager;
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
	
#			array_unshift($content, sprintf($jsTemplate, $baseUrl . 'wp-includes/js/underscore.min.js?ver=1.8.3'));
#			array_unshift($content, sprintf($jsTemplate, $baseUrl . 'wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'));
#			array_unshift($content, sprintf($jsTemplate, $baseUrl . 'wp-includes/js/jquery/jquery.js?ver=1.12.4'));
	
			$content = implode("\n", $content);
			
			$scripts = array();
			$scriptRegex = '<script.*<\/script>';
			$regexes = array(
				'<!--\[[a-zA-Z0-9 ]{1,}\]>[\s]{0,}' . $scriptRegex . '[\s]{0,}<!\[endif\]-->',
				$scriptRegex
			);
		
			foreach($regexes as $regex) {
				if (preg_match_all('/' . $regex . '/sUi', $content, $matches)) {
					foreach($matches[0] as $v) {
						$content = str_replace($v, '', $content);
						$scripts[] = $v;
					}
				}
			}
			
			$requireConfig = array(
				'jquery-migrate' => 'http://m2.fishpig.co.uk/wp/wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1',
			);

			$js = "require(['jquery', 'jquery-migrate', 'underscore'], function(jQuery, jQueryMigrate, _) {\n  %s\n});";
			$level = 1;
			
			foreach($scripts as $script) {
				$tabs = str_repeat("  ", $level);
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $src)) {
					$src = $src[1];
					$alias = basename($src);
					
					if (strpos($alias, '?') !== false) {
						$alias = substr($alias, 0, strpos($alias, '?'));
					}
					
					$alias = str_replace('.', '_', basename(basename($alias, '.js'), '.min'));
					
					$requireConfig[$alias] = $src;
					
					$js = sprintf($js, $tabs. "require(['" . $alias . "'], function() {\n" . $tabs . "%s\n" . $tabs . "});" . "\n");
				$level++;
				}
				else {
					$js = sprintf($js, strip_tags($script) . "\n%s\n");
				}
				

			}
			
			$js = str_replace('%s', '', $js);
			
			$requireConfigJs = "requirejs.config({\n  \"paths\": {\n    ";
				
			foreach($requireConfig as $alias => $path) {
				$requireConfigJs .= '"' . $alias . '": "' . $path . '",' . "\n    ";
				}
				
			$requireConfigJs = rtrim($requireConfigJs, "\n ,") . "\n  }\n" . '});';

#echo '<pre>' . $js;exit;
			$content .= sprintf("<script type=\"text/javascript\">" . $requireConfigJs . "
%s

					/*
					var _backup = {
						requirejs: window.requirejs,
						require: window.require,
						define: window.define
					};
					
					window.requirejs = undefined;
					window.require = undefined;
					window.define = undefined;
  */
  		;


			</script>", $js);

			$observer->getEvent()
					->getResponse()
						->setBody(str_replace('</body>', $content . '</body>', $bodyHtml));
		}
		
		return $this;
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
