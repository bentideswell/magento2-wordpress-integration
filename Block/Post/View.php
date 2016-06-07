<?php
/**
 *
**/

namespace FishPig\WordPress\Block\Post;

class View extends \FishPig\WordPress\Block\Post
{
	protected function _prepareLayout()
	{
		$this->_viewHelper->applyPageConfigData($this->pageConfig, $this->getPost());
        
		return parent::_prepareLayout();
	}
	
	protected function _toHtml()
	{
		return $this->getChildHtml();
	}
}
