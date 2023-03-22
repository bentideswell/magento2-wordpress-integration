<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Repository;

use Magento\Framework\Exception\NoSuchEntityException;

abstract class DataSourceRepository extends AbstractRepository
{
    /**
     * @auto
     */
    protected $dataSource = null;

    /**
     * @auto
     */
    protected $storeManager = null;

    /**
     * @var []
     */
    private $objects = [];

    /**
     * @param \FishPig\WordPress\Model\PostFactory $postFactory
     */
    public function __construct(
        \FishPig\WordPress\Api\Data\PostTypeTaxonomyDataSourceInterface $dataSource,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->dataSource = $dataSource;
        $this->storeManager = $storeManager;
    }

    /**
     * @param  string $taxonomy
     * @return bool
     */
    public function exists(string $id): bool
    {
        try {
            $this->get($id);

            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }

    /**
     * @param  string $id
     * @return mixed
     */
    public function get($id): \Magento\Framework\DataObject
    {
        $objects = $this->getAll();

        if (!isset($objects[$id])) {
            throw new NoSuchEntityException(
                __(
                    "The object (%1) that was requested doesn't exist. Verify the object and try again.",
                    $id
                )
            );
        }

        return $objects[$id];
    }

    /**
     * @return []
     */
    public function getAll(): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->objects[$storeId])) {
            $this->objects[$storeId] = [];

            foreach ($this->dataSource->getAll() as $id => $data) {
                $this->objects[$storeId][$id] = $this->getObjectFactory()->create()->setData($data);
            }
        }

        return $this->objects[$storeId];
    }
}
