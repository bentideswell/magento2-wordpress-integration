<?php

namespace FishPig\WordPress\Block\Sidebar\Widget;

class Text extends AbstractWidget
{
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return null;
	}
	
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/text.phtml');
		}
		
		return parent::_beforeToHtml();
	}

	/**
	 * Convert {{block tags to HTML
	 *
	 * @return string
	 */
	protected function _toHtmlIgnore()
	{
		if ($html = parent::_toHtml()) {
			return Mage::helper('cms')->getBlockTemplateProcessor()->filter($html);
		}
		
		return '';
	}
}
