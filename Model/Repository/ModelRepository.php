<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Repository;

use Magento\Framework\Exception\NoSuchEntityException;

abstract class ModelRepository
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     *
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        string $factoryClass,
        string $idFieldName = 'ID'
    ) {
        $this->storeManager = $storeManager;
        $this->objectFactory = $objectManager->get($factoryClass);
        $this->idFieldName = $idFieldName;
    }
    
    /**
     *
     */
    public function get($id): \Magento\Framework\DataObject
    {
        $storeId = (int)$this->getStoreId();
        $id = (int)$id;
        
        if (!isset($this->cache[$storeId])) {
            $this->cache[$storeId] = [];
        }

        if (isset($this->cache[$storeId][$id])) {
            if ($this->cache[$storeId][$id] === false) {
                throw new NoSuchEntityException(
                    __(
                        "The %1 (%2=%3) that was requested doesn't exist. Verify the object and try again.",
                        get_class($this->objectFactory->create()),
                        $this->idFieldName,
                        $id
                    )
                );
            }

            return $this->cache[$storeId][$id];
        }
        
        $this->cache[$storeId][$id] = false;

        $object = $this->loadObject($id, $this->idFieldName);
        
        return $this->cache[$storeId][$id] = $object;
    }
    
    /**
     * @param  mixed $id
     * @return false|\Magento\Framework\DataObject
     */
    public function getQuietly($id)
    {
        try {
            return $this->get($id);
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
    
    /**
     *
     */
    public function getWithoutException($id): \Magento\Framework\DataObject
    {
        $storeId = (int)$this->getStoreId();
        $id = (int)$id;
        
        if (isset($this->cache[$storeId][$id])) {
            return $this->cache[$storeId][$id];
        }
        
        if (!isset($this->cache[$storeId])) {
            $this->cache[$storeId] = [];
        }
        
        $this->cache[$storeId][$id] = false;

        return $this->cache[$id][$storeId] = $this->loadObject($id, $this->idFieldName);
    }
    
    /**
     * @param  mixed  $value
     * @param  string $field
     * @return \FishPig\WordPress\Api\Data\ViewableModelInterface
     */
    public function getByField($value, $field)
    {
        $storeId = (int)$this->getStoreId();
        
        if (!empty($this->cache[$storeId])) {
            foreach ($this->cache[$storeId] as $object) {
                if ($object->getData($field) === $value) {
                    return $object;
                }
            }
        } else {
            $this->cache[$storeId] = [];
        }

        $object = $this->loadObject($value, $field);
        
        return $this->cache[$storeId][$object->getId()] = $object;
    }
    
    /**
     * @param  mixed  $value
     * @param  string $field
     * @return \FishPig\WordPress\Api\Data\ViewableModelInterface
     */
    private function loadObject($value, $field)
    {
        $object = $this->objectFactory->create();

        if (!$object->load($value, $field)->getId()) {
            throw new NoSuchEntityException(
                __(
                    "The %1 (%2=%3) that was requested doesn't exist. Verify the object and try again.",
                    get_class($object),
                    $field,
                    $value
                )
            );
        }

        return $object;
    }
    
    /**
     * @return int
     */
    private function getStoreId(): int
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
