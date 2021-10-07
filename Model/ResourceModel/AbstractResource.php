<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use FishPig\WordPress\Model\Context as WPContext;

abstract class AbstractResource extends AbstractDb
{
    /**
     *
     */
    protected $wpContext;

    /**
     *
     */
    protected $resourceConnection = null;

    /**
     *
     */
    protected $tableAlias = 'main_table';

    /**
     *
     */
    public function __construct(Context $context, WPContext $wpContext, $connectionName = null)
    {
        $this->wpContext = $wpContext;
        $this->factory = $wpContext->getFactory();
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
     * @return
     */
    public function getTable($tableName)
    {
        return $this->resourceConnection->getTable($tableName);
        ;
    }

    /**
     * @return
     */
    public function getTablePrefix()
    {
        return $this->resourceConnection->getTablePrefix();
    }
    
    /**
     *
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        return $this->filterLoadSelect(
            $this->getConnection()
                ->select()
                    ->from(
                        [$this->tableAlias => $this->getMainTable()]
                    )->where(
                        $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->tableAlias, $field)) . '=?',
                         $value
                    ),
            $object
        );
    }
    
    /**
     *
     */
    public function filterLoadSelect($select, $object = null)
    {
        return $select;
    }
}
