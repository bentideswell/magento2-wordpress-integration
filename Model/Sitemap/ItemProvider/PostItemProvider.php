<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

class PostItemProvider extends AbstractItemProvider
{
    /**
     * @auto
     */
    protected $collectionFactory = null;

    /**
     *
     */
    const PRIORITY = 0.5;
    const CHANGE_FREQUENCY = 'monthly';

    /**
     *
     */
    public function __construct(
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $itemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Logger $logger,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($itemFactory, $storeManager, $logger);
    }

    /**
     *
     */
    protected function getCollection($storeId): iterable
    {
        return $this->collectionFactory->create()->addIsViewableFilter();
    }

    /**
     * Get the post imaages as an array
     *
     * @param  PostModel $post
     * @return ?\Magento\Framework\DataObject
     */
    public function getPostImages(\FishPig\WordPress\Model\Post $post): ?\Magento\Framework\DataObject
    {
        if ($image = $post->getImage()) {
            return new \Magento\Framework\DataObject(
                [
                    'collection' => [new \Magento\Framework\DataObject(['url' => $image->getFullSizeImage()])],
                    'title' => $post->getName(),
                    'thumbnail' => $image->getAvailableImage(),
                ]
            );
        }

        return null;
    }

    /**
     *
     */
    public function getImages($item): ?\Magento\Framework\DataObject
    {
        return $this->getPostImages($item);
    }

    /**
     *
     */
    public function getModifiedDate($item): string
    {
        return $item->getPostModifiedDate('Y-m-d');
    }
}
