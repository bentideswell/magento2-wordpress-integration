<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace FishPig\WordPress\Block\System\Config;

class Integrate extends \Magento\Backend\Block\Template
{
	protected $_app = null;
	protected $_wpUrlBuilder = null;
	
    public function __construct(
    	\Magento\Backend\Block\Template\Context $context,
    	\FishPig\WordPress\Model\AppFactory $appFactory, 
    	\FishPig\WordPress\Model\App\Url $urlBuilder,
    	array $data = []
    ) {
	    parent::__construct($context, $data);

	    $this->_wpUrlBuilder = $urlBuilder;	    

		if ($this->_request->getParam('section') === 'wordpress') {
		    $this->_app = $appFactory->create();;
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