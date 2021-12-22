<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class PostType implements \FishPig\WordPress\Api\Data\PostTypeTaxonomyDataSourceInterface
{
    /**
     * @var
     */
    private $dataRetrievers;

    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @param  \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param  array $dataRetrievers = []
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $dataRetrievers = []
    ) {
        $this->dataRetrievers = $dataRetrievers;
        $this->storeManager = $storeManager;
    }

    /**
     * @param  string $typeId = null
     * @return array|false
     */
    public function get($id)
    {
        $objects = $this->getAll();

        return isset($objects[$id]) ? $objects[$id] : false;
    }

    /**
     * @return []
     */
    public function getAll(): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (isset($this->objects[$storeId])) {
            return $this->objects[$storeId];
        }

        $this->objects[$storeId] = [];

        foreach ($this->dataRetrievers as $dataRetriever) {
            $this->objects[$storeId][] = $dataRetriever->getData();
        }

        return $this->objects[$storeId] = array_merge(...$this->objects[$storeId]);
    }
}
