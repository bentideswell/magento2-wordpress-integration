<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel\Menu;

class Item extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{

	public function _construct()
	{
		$this->_init('wordpress/menu_item', 'ID');
	}
}
