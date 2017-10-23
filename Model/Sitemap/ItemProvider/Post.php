<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

class Post extends AbstractItemProvider
{
	/*
	 *
	 */
	protected function _getItems($storeId)
	{
		$collection = $this->wpFactory->getFactory('FishPig\WordPress\Model\ResourceModel\Post\Collection')->create();
		$items = [];
  
		foreach($collection as $post) {
			$postImages = [];
			
			if ($image = $post->getImage()) {
				$postImages = new \Magento\Framework\DataObject([
					'collection' => [new \Magento\Framework\DataObject(['url' => $image->getFullSizeImage()])],
					'title' => $post->getName(),
					'thumbnail' => $image->getAvailableImage(),
				]);
			}
			
			$items[] = $this->itemFactory->create([
				'url' => $post->getUrl(),
				'updatedAt' => $post->getPostModifiedDate('Y-m-d'),
				'images' => $postImages,
				'priority' => 0.5,
				'changeFrequency' => 'monthly',
			]);			
		}
		
		return $items;
	}
}