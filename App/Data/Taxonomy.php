<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Data;

class Taxonomy
{
    /**
     * @var
     */
    private $dataRetrievers;

    /**
     * @var array
     */
    protected $taxonomies = [];

    /**
     * @param  \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param  array $postTypeRetrievers = []
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
     * @return TaxonomyModel|false
     */
    public function get($taxonomyType = null)
    {
        $taxonomies = $this->getAll();

        if (!$taxonomyType) {
            return $taxonomies;
        }

        return isset($taxonomies[$taxonomyType]) ? $taxonomies[$taxonomyType] : false;
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

        if (isset($this->taxonomies[$storeId])) {
            return $this->taxonomies[$storeId];
        }

        $this->taxonomies[$storeId] = [];

        foreach ($this->dataRetrievers as $dataRetriever) {
            $this->taxonomies[$storeId] = array_merge_recursive(
                $this->taxonomies[$storeId],
                $dataRetriever->getData()
            );
        }

        return $this->taxonomies[$storeId];
    }
}
