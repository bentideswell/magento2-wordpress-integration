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
		$this->getPost()->applyPageConfigData($this->pageConfig);
        
		return parent::_prepareLayout();
	}

	/*
	 *
	 *
	 */	
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$postType = $this->getPost()->getTypeInstance();
			$this->setTemplate('FishPig_WordPress::post/view.phtml');

			if ($postType->getPostType() !== 'post') {
				$postTypeTemplate = 'FishPig_WordPress::' . $postType->getPostType() . '/view.phtml';

				if ($this->getTemplateFile($postTypeTemplate)) {
					$this->setTemplate($postTypeTemplate);
				}
			}
		}
		
		return parent::_beforeToHtml();
	}
}
