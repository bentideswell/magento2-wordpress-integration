<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel\Menu;

class Collection extends \FishPig\WordPress\Model\ResourceModel\AbstractCollection
{

	public function _construct()
	{
		$this->_init('wordpress/menu');
	}
	
	/**
	 * Filter the term collection so that only nav_menu's are included
	 *
	 * @return $this
	 */
	protected function _initSelect()
	{
		parent::_initSelect();
		
		$this->getSelect()
			->where('taxonomy.taxonomy=?', $this->getNewEmptyItem()->getTaxonomy());
			
		return $this;
	}
}
