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
        \FishPig\WordPress\Model\ResourceModel\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\HierarchicalUrlGenerator $hierarchicalUrlGenerator,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $wpContext->getResourceConnection();
        $this->hierarchicalUrlGenerator = $hierarchicalUrlGenerator;
        $this->storeManager = $storeManager;
    }
    
    /**
     * @param  TaxonomyModel $taxonomy
     * @return array
     */
    public function getAllRoutes(TaxonomyModel $taxonomy): array
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $cacheKey = $storeId . '::get_all_routes::' . $taxonomy->getTaxonomy();
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $this->cache[$cacheKey] = [];

        $results = $this->resourceConnection->getConnection()->fetchAll(
            $this->getSelectForGetAllUris($taxonomy)
        );
    
        if ($results) {
            $slug = $taxonomy->getSlug();

            if ($taxonomy->isRewriteHierarchical()) {
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
      * @param  TaxonomyModel $taxonomy
      * @return
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
                $connection->quoteInto(
                    "tax.term_id = term.term_id AND tax.taxonomy = ?",
                    $taxonomy->getTaxonomy()
                ),
                'parent'
            );
            
        return $select;
    }
    
    /**
     * @param  TaxonomyModel $taxonomy
     * @return array
     */
    public function getRedirectableUris(TaxonomyModel $taxonomy): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                ['term' => $this->resourceConnection->getTable('terms')],
                ['id' => 'term_id', 'url_key' => 'slug']
            )
            ->join(
                ['tax' => $this->resourceConnection->getTable('term_taxonomy')],
                $connection->quoteInto("tax.term_id = term.term_id AND tax.taxonomy = ?", $taxonomy->getTaxonomy()),
                null
            )
            ->where('tax.parent > 0');

        if (!($redirectableUris = $connection->fetchAll($select))) {
            return [];
        }

        foreach ($redirectableUris as &$redirectableUri) {
            $redirectableUri['parent'] = 0;
        }
        
        // These are the URIs we redirect to
        $targetUris = $this->hierarchicalUrlGenerator->generateRoutes($redirectableUris, $taxonomy->getSlug());
        $redirectableData = [];

        if (!($allUris = $this->getAllRoutes($taxonomy))) {
            return [];
        }

        foreach ($redirectableUris as $redirectableUri) {
            if (isset($targetUris[$redirectableUri['id']], $allUris[$redirectableUri['id']])) {
                $redirectableData[$redirectableUri['id']] = [
                    'source' => $targetUris[$redirectableUri['id']],
                    'target' => $allUris[$redirectableUri['id']],
                ];
            }
        }

        return $redirectableData;
    }
}
