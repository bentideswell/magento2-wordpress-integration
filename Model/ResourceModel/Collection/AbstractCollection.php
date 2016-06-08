<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model\ResourceModel\Collection;

abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	public function getApp()
	{
		return $this->getResource()->getApp();
	}
	
	public function getConnection()
	{
		return $this->getResource()->getConnection();
	}
	
	/**
	 * Removes all order data set at the collection level
	 * This does not remove order set using self::getSelect()->order($field, $dir)
	 *
	 * @return $this
	 */
	public function resetOrderBy()
	{
		$this->_orders = array();
		
		return $this;
	}

	/**
	 * After loading a collection, dispatch the pre-set event
	 *
	 * @return $this
	 */
	protected function _afterLoad()
	{
		if ($this->getFlag('after_load_event_name')) {
			$this->_eventManager->dispatch($this->getFlag('after_load_event_name'), [
				'collection' => $this,
				'wrapper_block' => $this->getFlag('after_load_event_block')
			]);
		}

		return parent::_afterLoad();
	}
}
