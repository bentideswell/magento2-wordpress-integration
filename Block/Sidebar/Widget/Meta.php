<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Sidebar\Widget;

class Meta extends AbstractWidget
{
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Meta');
	}
	
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/meta.phtml');
		}
		
		return parent::_beforeToHtml();
	}
}
