<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

class HierarchicalUrlGenerator
{
    /**
     * Generate an array of URI's based on $results
     *
     * @param  array $results
     * @return array|false
     */
    public function generateRoutes(array $results, string $prefix = '')
    {
        $objects = [];
        $byParent = [];

        foreach ($results as $key => $result) {
            if (!$result['parent']) {
                $objects[$result['id']] = $result;
            } else {
                if (!isset($byParent[$result['parent']])) {
                    $byParent[$result['parent']] = [];
                }

                $byParent[$result['parent']][$result['id']] = $result;
            }
        }

        if (count($objects) === 0) {
            return false;
        }

        $routes = [];

        foreach ($objects as $objectId => $object) {
            if (($children = $this->createArrayTree($objectId, $byParent)) !== false) {
                $objects[$objectId]['children'] = $children;
            }

            $routes += $this->createLookupTable($objects[$objectId], $prefix);
        }

        return $routes;
    }

    /**
     * Create a lookup table from an array tree
     *
     * @param  array  $node
     * @param  string $idField
     * @param  string $field
     * @param  string $prefix  = ''
     * @return array
     */
    private function createLookupTable(&$node, $prefix = ''): array
    {
        if (!isset($node['id'])) {
            return [];
        }

        $urls = [
            $node['id'] => ltrim($prefix . '/' . urldecode($node['url_key']), '/')
        ];

        if (isset($node['children'])) {
            foreach ($node['children'] as $childId => $child) {
                $urls += $this->createLookupTable($child, $urls[$node['id']]);
            }
        }

        return $urls;
    }

    /**
     * Create an array tree. This is used for creating static URL lookup tables
     * for categories and pages
     *
     * @param  int    $id
     * @param  array  $pool
     * @param  string $field = 'parent'
     * @return false|array
     */
    private function createArrayTree($id, &$pool)
    {
        if (isset($pool[$id]) && $pool[$id]) {
            $children = $pool[$id];

            unset($pool[$id]);

            foreach ($children as $childId => $child) {
                unset($children[$childId]['parent']);
                if (($result = $this->createArrayTree($childId, $pool)) !== false) {
                    $children[$childId]['children'] = $result;
                }
            }

            return $children;
        }

        return false;
    }
}
