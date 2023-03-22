<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\ResourceModel\Meta\Collection\AbstractMetaCollection;

class MetaDataProvider implements \FishPig\WordPress\Api\Data\MetaDataProviderInterface
{
    /**
     * @auto
     */
    protected $resourceConnection = null;

    /**
     * @auto
     */
    protected $tableName = null;

    /**
     * @auto
     */
    protected $objectField = null;

    /**
     * @auto
     */
    protected $primaryKeyField = null;

    /**
     * @auto
     */
    protected $useKeyPrefix = null;

    /**
     * @var []
     */
    const COLLECTION_FLAG = '_meta_fields_joined';

    /**
     * @var array
     */
    private $cache = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\ResourceConnection $resourceConnection,
        string $tableName,
        string $objectField,
        string $primaryKeyField = 'meta_id',
        bool $useKeyPrefix = false
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->tableName = $tableName;
        $this->objectField = $objectField;
        $this->primaryKeyField = $primaryKeyField;
        $this->useKeyPrefix = $useKeyPrefix;
    }

    /**
     * @param  \FishPig\WordPress\Model\AbstractMetaModel $object
     * @param  string $key
     * @return mixed
     */
    public function getValue(
        \FishPig\WordPress\Model\AbstractMetaModel $object,
        string $key
    ) {
        $cacheKey = (int)$object->getId() . '--' . $key;

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $this->cache[$cacheKey] = false;

        $db = $this->resourceConnection->getConnection();

        if ($metaTable = $this->getMetaTable()) {
            $select = $db->select()
                ->from($metaTable, 'meta_value')
                ->where($this->objectField . '=?', (int)$object->getId())
                ->where('meta_key=?', $this->processMetaKey($key))
                ->limit(1);

            if (($value = $db->fetchOne($select)) !== false) {
                return $this->cache[$cacheKey] = is_string($value) ? trim($value) : $value;
            }
        }

        return null;
    }

    /**
     * @return string
     */
    private function getMetaTable(): string
    {
        if ($this->tableName) {
            return $this->resourceConnection->getTable(
                $this->tableName,
                !$this->useKeyPrefix
            );
        }

        return '';
    }

    /**
     * @param  string $key
     * @return string
     */
    private function processMetaKey(string $key): string
    {
        if (!$this->useKeyPrefix || !$key) {
            return $key;
        }

        if (($tablePrefix = $this->resourceConnection->getTablePrefix()) === 'wp_') {
            return $key;
        }

        if (preg_match('/^(wp_)(.*)$/', $key, $matches)) {
            return $tablePrefix . $matches[2];
        }

        return $key;
    }

    /**
     * An array of all of the meta fields that have been joined to this collection
     *
     * @var array
     */
    protected $metaFieldsJoined = [];

    /**
     * @param  AbstractMetaCollection $collection
     * @param  string $metaKey
     */
    public function addMetaFieldToSelect(AbstractMetaCollection $collection, $metaKey): void
    {
        if (($field = $this->joinMetaField($collection, $metaKey)) !== false) {
            $collection->getSelect()->columns([$metaKey => $field]);
        }
    }

    /**
     * @param  AbstractMetaCollection $collection
     * @param  string       $field
     * @param  string|array $filter
     * @return $this
     */
    public function addMetaFieldToFilter(AbstractMetaCollection $collection, $metaKey, $filter): void
    {
        if (($field = $this->joinMetaField($collection, $metaKey)) !== false) {
            $collection->addFieldToFilter($field, $filter);
        }
    }

    /**
     * @param  AbstractMetaCollection $collection
     * @param  string $field
     * @param  string $dir = 'asc'
     * @return $this
     */
    public function addMetaFieldToSort(AbstractMetaCollection $collection, $field, $dir = 'asc'): void
    {
        $collection->getSelect()->order($field . ' ' . $dir);
    }

    /**
     * @param  AbstractMetaCollection $collection
     * @param  string $field
     * @return $this
     */
    private function joinMetaField(AbstractMetaCollection $collection, $field)
    {
        $existingMetaJoins = $collection->getFlag(self::COLLECTION_FLAG) ?? [];

        if (!isset($existingMetaJoins[$field])) {
            if (!($metaTable = $this->getMetaTable())) {
                return false;
            }

            $model = $collection->getNewEmptyItem();
            $alias = 'meta_field_' . str_replace('-', '_', $field);
            $meta = new \Magento\Framework\DataObject([
                'key' => $field,
                'alias' => $alias,
            ]);

            /*$this->_eventManager->dispatch(
                $model->getEventPrefix() . '_join_meta_field',
                ['collection' => $this, 'meta' => $meta]
            );*/

            if ($meta->getCanSkipJoin()) {
                $existingMetaJoins[$field] = $meta->getAlias();
            } else {
                $condition = "{$alias}.{$this->objectField}"
                             . "=main_table.{$model->getResource()->getIdFieldName()} AND "
                             . $collection->getConnection()->quoteInto("{$alias}.meta_key=?", $field);

                $collection->getSelect()->joinLeft([$alias => $metaTable], $condition, '');
                $existingMetaJoins[$field] = $alias . '.meta_value';
            }

            $collection->setFlag(self::COLLECTION_FLAG, $existingMetaJoins);
        }

        return $existingMetaJoins[$field];
    }
}
