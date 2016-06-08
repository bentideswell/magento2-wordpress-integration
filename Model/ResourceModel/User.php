<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel;

class User extends \FishPig\WordPress\Model\ResourceModel\Meta\AbstractMeta
{
	public function _construct()
	{
		$this->_init('wordpress_user', 'ID');
	}
}
