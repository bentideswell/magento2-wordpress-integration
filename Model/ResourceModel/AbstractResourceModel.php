<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

abstract class AbstractResourceModel extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \FishPig\WordPress\App\ResourceConnection
     */
    private $resourceConnection = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context, 
        \FishPig\WordPress\Model\ResourceModel\Context $wpContext,
        $connectionName = null
    ) {
        $this->resourceConnection = $wpContext->getResourceConnection();
        parent::__construct($context, $connectionName);
    }

    /**
     * @return
     */
    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @return string
     */
    public function getTable($tableName)
    {
        return $this->resourceConnection->getTable($tableName);
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->resourceConnection->getTablePrefix();
    }
    
    /**
     * @return \FishPig\WordPress\App\ResourceConnection
     */
    public function getResourceConnection()
    {
        return $this->resourceConnection;
    }
    
    /**
     *
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['main_table' => $this->getMainTable()]
            )->where(
                $connection->quoteInto(
                    $connection->quoteIdentifier('main_table.' . $field) . '=?',
                    $value
                )
            );

        return $this->filterLoadSelect($select, $object);
    }
    
    /**
     * @return \Magento\Framework\DB\Select
     */
    public function filterLoadSelect(\Magento\Framework\DB\Select $select, $object = null): \Magento\Framework\DB\Select
    {
        return $select;
    }
}
