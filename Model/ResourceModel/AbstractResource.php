<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \FishPig\WordPress\App\ResourceConnection
     */
    private $resourceConnection = null;
    
    /**
     * @var \FishPig\WordPress\Api\Data\MetaDataProviderInterface
     */
    private $metaDataProvider = null;

    /**
     *
     */
    protected $tableAlias = 'main_table';

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context, 
        \FishPig\WordPress\Model\ResourceModel\Context $wpContext,
        $connectionName = null,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider = null
    ) {
        $this->resourceConnection = $wpContext->getResourceConnection();
        $this->metaDataProvider = $metaDataProvider;
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
    
    /**
     * @param  \FishPig\WordPress\Api\Data\ViewableInterface $object
     * @param  string $key
     * @param  mixed $default = null
     * @return mixed
     */
    public function getMetaValue(\FishPig\WordPress\Api\Data\ViewableInterface $object, string $key, $default = null)
    {
        return $this->metaDataProvider ? $this->metaDataProvider->getValue($object, $key, $default) : $default;
    }
}
