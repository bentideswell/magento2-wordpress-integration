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
			$postTypeInstance = $this->getPost()->getTypeInstance();
			$this->setTemplate('FishPig_WordPress::post/view.phtml');

			if ($postTypeInstance->getData('post_type') !== 'post') {
				$postTypeTemplate = 'FishPig_WordPress::' . $postTypeInstance->getData('post_type') . '/view.phtml';

				if ($this->getTemplateFile($postTypeTemplate)) {
					$this->setTemplate($postTypeTemplate);
				}
			}
		}
		
		return parent::_beforeToHtml();
	}
}
