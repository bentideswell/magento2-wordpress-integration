<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FishPig\WordPress\Block\System\Config;

class Integrate extends \Magento\Backend\Block\Template
{
	/**
	 * @
	**/
	protected $_app = null;
	
	/**
	 * @
	**/
	protected $_wpUrlBuilder = null;
	
	/**
	 * @
	**/
	protected $_storeManager = null;
	
	/**
	 * @
	**/
	protected $_emulator = null;
	
	/**
	 * @
	**/
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\FishPig\WordPress\Model\AppFactory $appFactory, 
    	\FishPig\WordPress\Model\App\Url $urlBuilder,
		\Magento\Store\Model\StoreManager $storeManager,
		\Magento\Store\Model\App\Emulation $emulator,
    	array $data = []
    ) {
	    parent::__construct($context, $data);

	    $this->_wpUrlBuilder = $urlBuilder;	   
	    $this->_storeManager = $storeManager; 
		$this->_emulator = $emulator;
		
		if ($this->_request->getParam('section') === 'wordpress') {
			try {
				$storeId = 0;

				if (($websiteId = (int)$this->_request->getParam('website')) !== 0) {
					$storeId = (int)$this->_storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
				}
				
				if ($storeId === 0) {
					$storeId = (int)$this->_request->getParam('store');
				}

				if ($storeId === 0) {
					$storeId = (int)$this->_storeManager->getDefaultStoreView()->getId();
				}

				$this->_emulator->startEnvironmentEmulation($storeId);
				
				$this->_app = $appFactory->create()->init();
				
				$this->_emulator->stopEnvironmentEmulation();
			}
			catch (\Exception $e) {
				$this->_emulator->stopEnvironmentEmulation();
			}
		}
	}
	
	protected function _toHtml()
	{
		if (!$this->_app) {
			return '';
		}

		if ($exception = $this->_app->getException()) {
			if ($exception instanceof \FishPig\WordPress\Model\App\Integration\Exception) {
				$msg = $exception->getFullMessage();
			}
			else {
				$msg = 'Unknown Error: ' . $exception->getMessage();
			}
			
			return sprintf('<div class="messages"><div class="message message-error error"><div>%s</div></div></div>', $msg);
		}
		
		$url = $this->_wpUrlBuilder->getUrl();
		$msg = sprintf('WordPress Integration is active. View your blog at <a href="%s" target="_blank">%s</a>.', $url, $url);

		return sprintf('<div class="messages"><div class="message message-success success"><div>%s</div></div></div>', $msg);		
	}
	
	protected function _prepareLayout()
	{
		return parent::_prepareLayout();
	}
}