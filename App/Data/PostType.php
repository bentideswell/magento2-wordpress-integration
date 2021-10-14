<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Data;

class PostType
{
    /**
     * @var
     */
    private $dataRetrievers;

    /**
     * @var array
     */
    protected $types = [];

    /**
     * @param  \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param  array $postTypeRetrievers = []
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\Model\PostTypeFactory $postTypeFactory,
        array $dataRetrievers = []
    ) {
        $this->storeManager = $storeManager;
        $this->postTypeFactory = $postTypeFactory;
        $this->dataRetrievers = $dataRetrievers;
    }

    /**
     * @param  string $typeId = null
     * @return array|false
     */
    public function get($typeId = null)
    {
        $types = $this->getAll();

        if (!$typeId) {
            return $types;
        }

        return isset($types[$typeId]) ? $types[$typeId] : false;
    }

    /**
     * @param  int $storeId = null
     * @return []
     */
    public function getAll(int $storeId = null): array
    {
        if ($storeId === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();
        }

        if (isset($this->types[$storeId])) {
            return $this->types[$storeId];
        }

        $this->types[$storeId] = [];

        foreach ($this->dataRetrievers as $dataRetriever) {
            $this->types[$storeId] = array_merge_recursive($this->types[$storeId], $dataRetriever->getData());
        }

        return $this->types[$storeId];
    }
}
