<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

use FishPig\WordPress\Model\Post as PostModel;

class Post extends AbstractItemProvider
{
    /**
     *
     */
    protected function _getItems($storeId)
    {
        $storeBaseUrl =  rtrim($this->storeManager->getStore()->getBaseUrl(), '/');
        $collection   = $this->factory->create('FishPig\WordPress\Model\ResourceModel\Post\Collection')->addIsViewableFilter();
        $items = [];

        foreach ($collection as $post) {
            if ($post->isContentBlock()) {
                continue;
            }

            $relativePostUrl = ltrim(str_replace($storeBaseUrl, '', $post->getUrl()), '/');

            if (!$relativePostUrl) {
                // Probably post_type=page and set as homepage
                // Don't add as Magento will add this URL for us
                continue;
            }

            if ($this->isPostNoIndex($post)) {
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
     * @return array
     */
    public function getPostImages(PostModel $post): ?\Magento\Framework\DataObject
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
     * Determine whether the post as noindex in it's robots tag
     *
     * @param  PostModel $post
     * @return bool
     */
    public function isPostNoIndex(PostModel $post): bool
    {
        $robots = strtoupper($post->getRobots());

        return strpos($robots, 'NOINDEX') !== false;
    }
}
