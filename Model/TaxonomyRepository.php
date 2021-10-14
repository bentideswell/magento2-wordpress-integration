<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class TaxonomyRepository
{
    /**
     * @var []
     */
    private $objects = [];

    /**
     * @param \FishPig\WordPress\Model\PostFactory $postFactory
     */
    public function __construct(
        \FishPig\WordPress\App\Data\Taxonomy $dataSource,
        \FishPig\WordPress\Model\TaxonomyFactory $objectFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->dataSource = $dataSource;
        $this->objectFactory = $objectFactory;
        $this->storeManager = $storeManager;
    }
    
    /**
     * @param  string $taxonomy 
     * @return bool
     */
    public function exists(string $taxonomy): bool
    {
        try {
            $this->get($taxonomy);
            
            return true;
        } catch (NoSuchEntityException $e) {
            return false;            
        }
    }
    
    /**
     * @param  string $taxonomy 
     * @return mixed
     */
    public function get(string $taxonomy = null)
    {
        $objects = $this->getAll();

        if ($taxonomy === null) {
            return $objects;
        }

        if (!isset($objects[$taxonomy])) {
            throw new NoSuchEntityException(
                __("The WordPress taxonomy ($taxonomy) that was requested doesn't exist. Verify the taxonomy and try again.")
            );
        }

        return $objects[$taxonomy];
    }

    /**
     * @return []
     */
    public function getAll(): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->cache[$storeId])) {
            $this->cache[$storeId] = [];

            foreach ($this->dataSource->getAll() as $id => $data) {
                $this->cache[$storeId][$id] = $this->objectFactory->create()->setData($data);
            }
        }

        return $this->cache[$storeId];
    }
}
