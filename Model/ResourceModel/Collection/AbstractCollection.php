<?php
/**
 *
 */
namespace FishPig\WordPress\Model\ResourceModel\Collection;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection as AbstractDbCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use FishPig\WordPress\Model\Context as WPContext;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

abstract class AbstractCollection extends AbstractDbCollection
{
    /**
     * @var WPContext
     */
    protected $wpContext;

    /**
     * @var OptionManager
     */
    protected $optionManager;

    /**
     * @vr
     */
    protected $postTypeManager;

    /**
     *
     *
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
               LoggerInterface $logger,
    FetchStrategyInterface $fetchStrategy,
          ManagerInterface $eventManager,
                             WPContext $wpContext,
          AdapterInterface $connection  = null,
                AbstractDb $resource    = null
    )
    {
        $this->wpContext       = $wpContext;
        $this->optionManager   = $wpContext->getOptionManager();
        $this->postTypeManager = $wpContext->getPostTypeManager();

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
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
     * Force the collection to be empty
     *
     */
    public function forceEmpty()
    {
        $this->getSelect()->where('1=2')->limit(1);

        return $this;
    }
}
