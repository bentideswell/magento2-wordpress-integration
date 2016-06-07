<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel;

class Menu extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{

	public function _construct()
	{
		$this->_init('wordpress/menu', 'term_id');
	}
}
