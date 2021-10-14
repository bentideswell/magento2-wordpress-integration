<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use Magento\Framework\Exception\NoSuchEntityException;

class PostTypeRepository
{
    /**
     * @var []
     */
    private $postTypes = [];

    /**
     * @param \FishPig\WordPress\Model\PostFactory $postFactory
     */
    public function __construct(
        \FishPig\WordPress\App\Data\PostType $postTypeDataSource,
        \FishPig\WordPress\Model\PostTypeFactory $postTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->postTypeDataSource = $postTypeDataSource;
        $this->postTypeFactory = $postTypeFactory;
        $this->storeManager = $storeManager;
    }
    
    /**
     *
     */
    public function get(string $type = null)
    {
        $postTypes = $this->getAll();

        if ($type === null) {
            return $postTypes;
        }

        if (!isset($postTypes[$type])) {
            throw new NoSuchEntityException(
                __("The WordPress post type ($type) that was requested doesn't exist. Verify the post type and try again.")
            );
        }

        return $postTypes[$type];
    }

    /**
     * @return []
     */
    public function getAll(): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->cache[$storeId])) {
            $this->cache[$storeId] = [];

            foreach ($this->postTypeDataSource->getAll() as $typeId => $postTypeData) {
                $this->cache[$storeId][$typeId] = $this->postTypeFactory->create()->setData($postTypeData);
            }
        }

        return $this->cache[$storeId];
    }
}
