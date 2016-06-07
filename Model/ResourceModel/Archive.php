<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel;

class Archive extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{

	/**
	 * Set the table and primary key
	 *
	 * @return void
	 */
	public function _construct()
	{
		$this->_init('wordpress_post', 'ID');
	}
}
