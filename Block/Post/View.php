<?php
/**
 *
**/

namespace FishPig\WordPress\Block\Post;

class View extends \FishPig\WordPress\Block\Post
{
	/*
	 *
	 *
	 */
	protected function _prepareLayout()
	{
		$this->_viewHelper->applyPageConfigData($this->pageConfig, $this->getPost());
        
		return parent::_prepareLayout();
	}

	/*
	 *
	 *
	 */	
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('FishPig_WordPress::post/view.phtml');
			
			if ($this->getPost()->getPostType() !== 'post') {
				$postTypeTemplate = 'FishPig_WordPress::' . $this->getPost()->getPostType() . '/view.phtml';

				if ($this->getTemplateFile($postTypeTemplate)) {
					$this->setTemplate($postTypeTemplate);
				}
			}
		}
		
		return parent::_beforeToHtml();
	}
}
