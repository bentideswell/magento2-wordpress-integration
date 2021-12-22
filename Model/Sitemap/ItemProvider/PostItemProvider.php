<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

class PostItemProvider implements \Magento\Sitemap\Model\ItemProvider\ItemProviderInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $collectionFactory,
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $itemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->itemFactory = $itemFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param  int $storeId
     * @return array
     */
    public function getItems($storeId)
    {
        $storeBaseUrl =  rtrim($this->storeManager->getStore()->getBaseUrl(), '/');
        $collection   = $this->collectionFactory->create()->addIsViewableFilter();
        $items = [];

        foreach ($collection as $post) {
            if (!$post->isPublic()) {
                continue;
            }

            $relativePostUrl = ltrim(str_replace($storeBaseUrl, '', $post->getUrl()), '/');

            if (!$relativePostUrl) {
                // Probably post_type=page and set as homepage
                // Don't add as Magento will add this URL for us
                continue;
            }

            if (!$post->isPublic()) {
                // Don't include posts that are set to noindex
                continue;
            }

            $items[] = $this->itemFactory->create(
                [
                    'url' => $relativePostUrl,
                    'updatedAt' => $post->getPostModifiedDate('Y-m-d'),
                    'images' => $this->getPostImages($post),
                    'priority' => 0.5,
                    'changeFrequency' => 'monthly',
                ]
            );
        }

        return $items;
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
}
