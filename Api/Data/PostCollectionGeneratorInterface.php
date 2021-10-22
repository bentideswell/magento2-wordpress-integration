<?php
/**
 *
 */
namespace FishPig\WordPress\Api\Data;

interface PostCollectionGeneratorInterface
{
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection;
}
