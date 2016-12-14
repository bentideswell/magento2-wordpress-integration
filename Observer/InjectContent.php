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
	
			array_unshift($content, sprintf($jsTemplate, $baseUrl . 'wp-includes/js/underscore.min.js?ver=1.6.0'));
			array_unshift($content, sprintf($jsTemplate, $baseUrl . 'wp-includes/js/jquery/jquery-migrate.min.js?ver=1.4.1'));
			array_unshift($content, sprintf($jsTemplate, $baseUrl . 'wp-includes/js/jquery/jquery.js?ver=1.12.4'));
	
			$observer->getEvent()
					->getResponse()
						->setBody(str_replace('</body>', implode('', $content) . '</body>', $bodyHtml));
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
