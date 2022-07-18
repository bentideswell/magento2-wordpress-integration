<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Meta\Collection;

abstract class AbstractMetaCollection extends \FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     *
     */
    private $metaDataProvider = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        string $modelName = null
    ) {
        $this->metaDataProvider = $metaDataProvider;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource, $modelName);
    }

    /**
     * @param  string|[] $metaKey
     * @return $this
     */
    public function addMetaFieldToSelect($metaKey): self
    {
        $this->metaDataProvider->addMetaFieldToSelect($this, $metaKey);
        return $this;
    }

    /**
     * Add a meta field to the filter (where) part of the query
     *
     * @param  string       $field
     * @param  string|array $filter
     * @return $this
     */
    public function addMetaFieldToFilter($metaKey, $filter): self
    {
        $this->metaDataProvider->addMetaFieldToFilter($this, $metaKey, $filter);
        return $this;
    }

    /**
     * Add a meta field to the SQL order section
     *
     * @param  string $field
     * @param  string $dir   = 'asc'
     * @return $this
     */
    public function addMetaFieldToSort($field, $dir = 'asc'): self
    {
        $this->metaDataProvider->addMetaFieldToSort($this, $field, $dir);
        return $this;
    }
}
