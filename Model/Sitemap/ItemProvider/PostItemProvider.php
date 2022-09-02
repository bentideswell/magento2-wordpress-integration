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
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        parent::__construct($itemFactory, $storeManager, $logger);
    }

    /**
     * @param  int $storeId
     * @return array
     */
    public function getItems($storeId)
    {
        $storeBaseUrl =  rtrim($this->storeManager->getStore()->getBaseUrl(), '/');
        $collection   = $this->postCollectionFactory->create()->addIsViewableFilter();
        $items = [];

        foreach ($collection as $post) {
            if (!$post->isPublic()) {
                // Don't include posts that are set to noindex in Yoast
                continue;
            }

            if (!($relativePostUrl = $this->makeUrlRelative($post->getUrl()))) {
                // Probably post_type=page and set as homepage
                // Don't add as Magento will add this URL for us
                continue;
            }

            if (!$this->canAddToSitemap($post)) {
                // This can be used by plugins, alhough it's better to use
                // Post::isPublic
                continue;
            }

            $items[] = $this->itemFactory->create(
                [
                    'url' => $relativePostUrl,
                    'updatedAt' => $post->getPostModifiedDate('Y-m-d'),
                    'images' => $this->getPostImages($post),
                    'priority' => $this->getPriority($post),
                    'changeFrequency' => $this->getChangeFrequency($post),
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
