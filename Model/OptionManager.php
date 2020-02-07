<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

class OptionManager
{   
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     *
     */
    public function __construct(ResourceConnection $resourceConnection, StoreManagerInterface $storeManager)
    {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager       = $storeManager;
    }

    /**
     * Get option value
     *
     * @param  string $key
     * @return mixed
     */
    public function getOption($key)
    {
        $storeId = $this->getStoreId();

        if (!isset($this->data[$storeId])) {
            $this->data[$storeId] = [];
        }

        if (!isset($this->data[$storeId][$key])) {
            $resource   = $this->resourceConnection;
            $connection = $resource->getConnection();

            $select = $connection->select()
                ->from($resource->getTable('wordpress_option'), 'option_value')
                ->where('option_name = ?', $key);

            $this->data[$storeId][$key] = $connection->fetchOne($select);
        }

        return $this->data[$storeId][$key];
    }

    /**
     *
     */
    public function optionExists($key)
    {
        $resource   = $this->resourceConnection;
        $connection = $resource->getConnection();

        $select = $connection->select()
            ->from($resource->getTable('wordpress_option'), 'option_value')
            ->where('option_name = ?', $key)
            ->limit(1);

        return $connection->fetchOne($select) !== false;
    }

    /**
     *
     */
    public function setOption($key, $value)
    {
        $storeId    = $this->getStoreId();
        $resource   = $this->resourceConnection;
        $connection = $resource->getConnection();
        $table      = $resource->getTable('wordpress_option');

        if ($this->optionExists($key)) {
            $connection->update(
                $table,
                ['option_value' => $value],
                $connection->quoteInto('option_name = ?', $key)
            );

            if (isset($this->data[$storeId][$key])) {
                unset($this->data[$storeId][$key]);
            }
        }
        else {
            $connection->insert($table, ['option_name' => $key, 'option_value' => $value]);
        }

        return $this;
    }

    /**
     * Get a site option.
     * This is implemented in Multisite
     *
     * @param  string $key
     * @return mixed
     */
    public function getSiteOption($key)
    {
        return false;
    }

    /**
     * Get the store ID
     *
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
