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
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \FishPig\WordPress\Model\Context $wpContext,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
	    
	    parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
	    
	    $this->_app = $wpContext->getApp();
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
