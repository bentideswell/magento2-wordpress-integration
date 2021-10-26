<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Collection;

abstract class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        string $modelName = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        
        if ($modelName) {
            $this->setModel($modelName);
        }
    }
    
    /**
     * @return void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->_eventManager->dispatch($this->_eventPrefix . '_init_select', [$this->_eventObject => $this]);
    }
    
    /**
     * @param string $table
     * @return $this
     */
    public function setMainTable($table)
    {
        $this->_mainTable = $table;

        return $this;
    }
}
