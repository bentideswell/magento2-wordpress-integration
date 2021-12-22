<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

use FishPig\WordPress\Model\PostType as PostTypeModel;

class PostType
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\ResourceModel\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\HierarchicalUrlGenerator $hierarchicalUrlGenerator,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $wpContext->getResourceConnection();
        $this->hierarchicalUrlGenerator = $hierarchicalUrlGenerator;
        $this->storeManager = $storeManager;
    }
    
    /**
     * @param  PostTypeModel $postType
     * @return array|false
     */
    public function getHierarchicalPostNames(PostTypeModel $postType)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $cacheKey = $storeId . '_hierarchical_post_names__' . $postType->getPostType();
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $this->cache[$cacheKey] = false;
        
        $db = $this->resourceConnection->getConnection();
        
        $select = $db->select()->from(
            $this->resourceConnection->getTable('posts'),
            [
                'id'      => 'ID',
                'url_key' =>  'post_name',
                'parent'  => 'post_parent'
            ]
        )->where(
            'post_type=?',
            $postType->getPostType()
        )->where(
            'post_status=?',
            'publish'
        );

        if ($routes = $db->fetchAll($select)) {
            $this->cache[$cacheKey] = $this->hierarchicalUrlGenerator->generateRoutes($routes);
        }
        
        return $this->cache[$cacheKey];
    }
}
