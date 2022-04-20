<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class MetaDataProvider implements \FishPig\WordPress\Api\Data\MetaDataProviderInterface
{
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
}
