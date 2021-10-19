<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

use FishPig\WordPress\Model\Taxonomy as TaxonomyModel;

class Taxonomy
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\ResourceConnection $resourceConnection,
        \FishPig\WordPress\Model\ResourceModel\HierarchicalUrlGenerator $hierarchicalUrlGenerator,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->hierarchicalUrlGenerator = $hierarchicalUrlGenerator;
        $this->storeManager = $storeManager;
    }
    
    public function getAllRoutes(TaxonomyModel $taxonomy)
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $cacheKey = $storeId . '_get_all_routes' . $taxonomy->getTaxonomy();
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $this->cache[$cacheKey] = false;        

        $results = $this->resourceConnection->getConnection()->fetchAll(
            $this->getSelectForGetAllUris($taxonomy)
        );

    
        if ($results) {
            $slug = $taxonomy->getSlug();

            if ($taxonomy->isHierarchical()) {
                $this->cache[$cacheKey] = $this->hierarchicalUrlGenerator->generateRoutes(
                    $results,
                    $slug
                );
            } else {
                $routes = [];

                foreach ($results as $result) {
                    $routes[$result['id']] = ltrim($slug . '/' . $result['url_key'], '/');
                }

                $this->cache[$cacheKey] = $routes;
            }
        }
        
        return $this->cache[$cacheKey];
    }

    /**
     *
     */
    public function getSelectForGetAllUris(TaxonomyModel $taxonomy)
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['term' => $this->resourceConnection->getTable('terms')],
                [
                    'id' => 'term_id',
                    'url_key' => 'slug',
                ]
            )
            ->join(
                ['tax' => $this->resourceConnection->getTable('term_taxonomy')],
                $connection->quoteInto("tax.term_id = term.term_id AND tax.taxonomy = ?", $taxonomy->getTaxonomyType()),
                'parent'
            );
            
        return $select;
    }
}
