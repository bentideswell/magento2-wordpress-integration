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
    	\FishPig\WordPress\Model\App $app, 
    	\FishPig\WordPress\Model\App\Url $urlBuilder,
    	array $data = []
    ) {
	    $this->_app = $app;
	    $this->_wpUrlBuilder = $urlBuilder;
	    
	    parent::__construct($context, $data);
	}
	
	protected function _toHtml()
	{
		if ($exception = $this->_app->getException()) {
			if ($exception instanceof \FishPig\WordPress\Model\App\Integration\Exception) {
				$msg = $exception->getMessage() . ' Error: ' . $exception->getRawErrorMessage();
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