<?php
/*
 *
 */
namespace FishPig\WordPress\Model\ResourceModel\Collection;

/* Parent Class */
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractDbCollection;

/* Constructor Args */
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use FishPig\WordPress\Model\OptionManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

abstract class AbstractCollection extends AbstractDbCollection
{
	/*
	 * @var OptionManager
	 */
	protected $optionManager;
	
	/*
	 *
	 *
	 */
	public function __construct(
		EntityFactoryInterface $entityFactory,
		       LoggerInterface $logger,
    FetchStrategyInterface $fetchStrategy,
          ManagerInterface $eventManager,
             OptionManager $optionManager,
          AdapterInterface $connection  = null,
                AbstractDb $resource    = null
	)
	{
		parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
		
		$this->optionManager = $optionManager;
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
