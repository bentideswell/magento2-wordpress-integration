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
		return $this->__('Meta');
	}
	
	/**
	 * Determine whether the current customer is logged in
	 *
	 * @return bool
	 */
	public function customerIsLoggedIn()
	{
		return Mage::getSingleton('customer/session')->isLoggedIn();
	}
}
