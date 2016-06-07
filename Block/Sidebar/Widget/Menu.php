<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Menu extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * Generate the Menu HTML
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		parent::_beforeToHtml();
		
		$menuBlock = $this->getLayout()->createBlock('wordpress/menu')
			->setMenuId($this->getNavMenu())
			->includeWrapper(true);

		$this->setMenuBlock($menuBlock)
			->setMenuHtml($menuBlock->toHtml())
			->setTitle($menuBlock->getTitle());

		return $this;
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return '';
	}
}
