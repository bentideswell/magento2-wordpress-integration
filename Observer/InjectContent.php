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
			
			$scripts = array(
				'<script type="text/javascript" src="' . $this->_app->getWpUrlBuilder()->getSiteUrl() . '/wp-includes/js/underscore.min.js"></script>',
			);
			
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
			
			// Check each JS file for define.amd and disable define before including then reenable after
			foreach($scripts as $skey => $script) {
				if (preg_match('/<script[^>]{1,}src=[\'"]{1}(.*)[\'"]{1}/U', $script, $matches)) {
					$externalScriptUrlFull = $matches[1];
					$externalScriptUrl = strpos($externalScriptUrlFull, '?') === false ? $externalScriptUrlFull : substr($externalScriptUrlFull, 0, strpos($externalScriptUrlFull, '?'));

					// Check that the script is a local file
					if (strpos($externalScriptUrl, $this->_app->getWpUrlBuilder()->getSiteUrl()) !== false) {
						$localScriptFile = $this->_app->getPath() . '/' . substr($externalScriptUrl, strlen($this->_app->getWpUrlBuilder()->getSiteUrl()));
						$scriptContent = file_get_contents($localScriptFile);

						// Check whether the script supports AMD
						if (strpos($scriptContent, 'define.amd') !== false) {
							$newScriptFile = $this->directoryList->getPath('media') . DIRECTORY_SEPARATOR . md5($externalScriptUrlFull) . '.js';
							$newScriptUrl = $this ->_storeManager-> getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . basename($newScriptFile);
							
							// Only write data if new script doesn't exist or local file has been updated
							if (!is_file($newScriptFile) || filemtime($localScriptFile) > filemtime($newScriptFile)) {
								file_put_contents(
									$newScriptFile, 
									"__d=define;define=null;\n" . $scriptContent . "\ndefine=__d;__d=null;"
								);
							}
							
							// Update script tag to use new URL					
							$scripts[$skey] = str_replace($externalScriptUrlFull, $newScriptUrl, $script);
						}
					}
				}
			}

			$content .= implode("\n", $scripts);

			$observer->getEvent()->getResponse()->setBody(str_replace('</body>', $content . '</body>', $bodyHtml));
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
