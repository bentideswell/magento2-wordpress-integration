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

            $postImages = [];

            if ($image = $post->getImage()) {
                $postImages = new \Magento\Framework\DataObject(
                    [
                    'collection' => [new \Magento\Framework\DataObject(['url' => $image->getFullSizeImage()])],
                    'title' => $post->getName(),
                    'thumbnail' => $image->getAvailableImage(),
                    ]
                );
            }

            $items[] = $this->itemFactory->create(
                [
                'url' => $relativePostUrl,
                'updatedAt' => $post->getPostModifiedDate('Y-m-d'),
                'images' => $postImages,
                'priority' => 0.5,
                'changeFrequency' => 'monthly',
                ]
            );
        }

        return $items;
    }
    
    /**
     * Determine whether the post as noindex in it's robots tag
     *
     * @param  PostModel $post
     * @return bool
     */
    private function isPostNoIndex(PostModel $post)
    {
        $robots = strtoupper($post->getRobots());
        
        return strpos($robots, 'NOINDEX') !== false;
    }
}
