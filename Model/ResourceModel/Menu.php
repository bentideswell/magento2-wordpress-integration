<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel;

use \FishPig\WordPress\Model\ResourceModel\Term;

class Menu extends Term
{
	public function _construct()
	{
		$this->_init('wordpress_menu', 'term_id');
	}
}
