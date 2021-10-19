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
        string $factoryClass,
        string $idFieldName = 'ID'
    ) {
        $this->objectFactory = $objectManager->get($factoryClass);
        $this->idFieldName = $idFieldName;
    }
    
    /**
     *
     */
    public function get($id): \Magento\Framework\DataObject
    {
        $id = (int)$id;
        
        if (isset($this->cache[$id])) {
            return $this->cache[$id];
        }
        
        $this->cache[$id] = false;

        $object = $this->loadObject($id, $this->idFieldName);
        
        return $this->cache[$id] = $object;
    }
    
    /**
     * @param  mixed  $value
     * @param  string $field     
     * @return \FishPig\WordPress\Api\Data\Entity\ViewableInterface
     */
    public function getByField($value, $field)
    {
        if ($this->cache) {
            foreach ($this->cache as $object) {
                if ($object->getData($field) === $value) {
                    return $object;
                }
            }
        }

        $object = $this->loadObject($value, $field);
        
        return $this->cache[$object->getId()] = $object;
    }
    
    /**
     * @param  mixed  $value
     * @param  string $field     
     * @return \FishPig\WordPress\Api\Data\Entity\ViewableInterface
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
}