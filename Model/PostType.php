<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\AbstractResourcelessModel;
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;
use FishPig\WordPress\Model\ResourceConnection;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\TaxonomyManager;
use FishPig\WordPress\Model\Factory;

class PostType extends AbstractResourcelessModel implements ViewableInterface
{
    /**
     *
     */
    const ENTITY = 'wordpress_post_type';

    /**
     * @const string
     */
    const CACHE_TAG = 'wordpress_post_type';

    /**
     * Cache of URI's for hierarchical post types
     *
     * @var array static
     */
    static $_uriCache = [];

    /**
     * Determine whether post type uses GUID links
     *
     * @return bool
     */
    public function useGuidLinks()
    {
        return trim($this->getData('rewrite/slug')) === '';
    }

    /**
     * Determine whether the post type is a built-in type
     *
     * @return bool
     */
    public function isDefault()
    {
        return (int)$this->_getData('_builtin') === 1;
    }

    /**
     * Get the permalink structure as a string
     *
     * @return string
     */
    public function getPermalinkStructure()
    {
        $structure = ltrim(str_replace('index.php/', '', ltrim($this->getData('rewrite/slug'), ' -/')), '/');

        if (!$this->isDefault() && strpos($structure, '%postname%') === false) {
            $structure = rtrim($structure, '/') . '/%postname%/';
        }

        if ($this->isHierarchical()) {
            $structure = str_replace('%postname%', '%postnames%', $structure);
        }

        if ($this->withFront() && ($front = $this->url->getFront())) {
            $structure = ltrim($front . '/' . $structure, '/');
        }

        return $structure;
    }

    /**
     * Does the URL include the front
     *
     * @return bool
     */
    public function withFront()
    {
        return (int)$this->getData('rewrite/with_front') === 1;
    }

    /**
     * Retrieve the permalink structure in array format
     *
     * @return false|array
     */
    public function getExplodedPermalinkStructure()
    {
        $structure = $this->getPermalinkStructure();
        $parts = preg_split("/(\/|-)/", $structure, -1, PREG_SPLIT_DELIM_CAPTURE);
        $structure = array();

        foreach($parts as $part) {
            if ($result = preg_split("/(%[a-zA-Z0-9_]{1,}%)/", $part, -1, PREG_SPLIT_DELIM_CAPTURE)) {
                $results = array_filter(array_unique($result));

                foreach($results as $result) {
                    array_push($structure, $result);
                }
            }
            else {
                $structure[] = $part;
            }
        }

        return $structure;
    }

    /**
     * Determine whether the permalink has a trailing slash
     *
     * @return bool
     */
    public function permalinkHasTrainingSlash()
    {
        return substr($this->getData('rewrite/slug'), -1) === '/' || substr($this->getPermalinkStructure(), -1) === '/';
    }

    /**
     * Retrieve the URL to the cpt page
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url->getUrl($this->getArchiveSlug() . '/');
    }

    /**
     * Retrieve the post collection for this post type
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection()
    {
        return $this->factory->create('Model\ResourceModel\Post\Collection')->addPostTypeFilter($this->getPostType());
    }

    /**
     * Get the archive slug for the post type
     *
     * @return string
     */    
    public function getSlug()
    {
        $slug = $this->getData('rewrite/slug');

        if ($this->withFront()) {
            $slug = $this->getFront() . '/' . $slug;
        }

        return $slug;
    }

    /**
     * Get the archive slug for the post type
     *
     * @return string
     */
    /**
     * Get the archive slug for the post type
     *
     * @return string
     */
    public function getArchiveSlug()
    {
        if (!$this->hasArchive()) {
            return false;
        }

        $slug = false;

        if (((string)$slug = $this->getHasArchive()) !== '1') {
            // Do nothing yet
        }
        else if ($slug = $this->getSlug()) {
            if (strpos($slug, '%') !== false) {
                $slug = trim(substr($slug, 0, strpos($slug, '%')), '%/');
            }
        }

        if (!$slug) {
            $slug = $this->getPostType();
        }

        return ltrim($slug, '/');
    }

    /**
     * Get the URL of the archive page
     *
     * @return string
     */
    public function getArchiveUrl()
    {
        return $this->hasArchive() ? $this->url->getUrl($this->getArchiveSlug() . '/') : '';
    }

    /**
     * Determine whether $taxonomy is supported by the post type
     *
     * @param string $taxonomy
     * @return bool
     */
    public function isTaxonomySupported($taxonomy)
    {
        return $this->getTaxonomies() ? in_array($taxonomy, $this->getTaxonomies()) : false;
    }

    /**
     * Get a taxonomy that is supported by the post type
     *
     * @return string
     */
    public function getAnySupportedTaxonomy($prioritise = array())
    {
        if (!is_array($prioritise)) {
            $prioritise = array($prioritise);
        }

        foreach($prioritise as $type) {
            if ($this->isTaxonomySupported($type)) {
                return $this->taxonomyManager->getTaxonomy($type);
            }
        }

        if ($taxonomies = $this->getTaxonomies()) {
            return $this->taxonomyManager->getTaxonomy(array_shift($taxonomies));
        }

        return false;
    }

    /**
     * Get the name of the post type
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData('labels/name');
    }

    /**
     * Determine whether this post type is hierarchical
     *
     * @return bool
     */
    public function isHierarchical()
    {
        return (int)$this->getData('hierarchical') === 1;
    }

    /**
     * Get the hierarchical post name for a post
     * This is the same as %postname% but with all of the parent post names included
     *
     * @param int $id
     * @return string|false
     */
    public function getHierarchicalPostName($id)
    {
        if ($routes = $this->getHierarchicalPostNames()) {
            return isset($routes[$id]) ? $routes[$id] : false;
        }

        return false;
    }

    /**
     * Get all routes (hierarchical)
     *
     * @return false|array
     */
    public function getAllRoutes()
    {
        return $this->getHierarchicalPostNames();
    }

    /**
     * Get an array of hierarchical post names
     *
     * @return false|array
     */
    public function getHierarchicalPostNames()
    {
        if (!$this->isHierarchical()) {
            return false;
        }

        $storeId = (int)$this->wpContext->getStoreManager()->getStore()->getId();

        if (isset(self::$_uriCache[$storeId][$this->getPostType()])) {
            return self::$_uriCache[$storeId][$this->getPostType()];
        }

        $resource = $this->wpContext->getResourceConnection();

        if (!($db = $resource->getConnection())) {
            return false;
        }

        if (!isset(self::$_uriCache[$storeId])) {
            self::$_uriCache[$storeId] = [];
        }

        $select = $db->select()
            ->from(['term' => $resource->getTable('wordpress_post')], [
                'id'      => 'ID',
                'url_key' =>  'post_name', 
                'parent'  => 'post_parent'
            ])
            ->where('post_type=?', $this->getPostType())
            ->where('post_status=?', 'publish');

        return self::$_uriCache[$this->getPostType()] = self::generateRoutesFromArray($db->fetchAll($select));
    }

    /**
     * @return bool
     */
    public function hasArchive()
    {
        return $this->getHasArchive() && $this->getHasArchive() !== '0';
    }

    /**
     * @return string
     */
    public function getPostType()
    {
        return $this->_getData('post_type') ? $this->_getData('post_type') : $this->_getData('name');
    }

    /**
     *
     *
     * @return string
     */
    public function getId()
    {
        return $this->getPostType();
    }

    /**
     * @return string
     */    
    public function getPluralName()
    {
        return $this->getData('labels/name');
    }

    /**
     * Determine whether post's of this type are included in the search
     *
     * @return bool
     */
    public function isSearchable()
    {
        return (int)$this->getData('exclude_from_search') === 0;
    }

    /**
     *
     *
     * @return array
     */
    public function getBreadcrumbStructure($post)
    {
        $tokens  = explode('/', trim($this->getSlug(), '/'));
        $objects = [];

        foreach($tokens as $token) {
            if ($token === $this->getPostType() || ($this->getArchiveSlug() && trim($this->getArchiveSlug(), '/') === $token)) {
                if (!$this->isDefault() && $this->hasArchive()) {
                    $objects['post_type'] = $this;
                }
            }
            else if (substr($token, 0, 1) === '%' && substr($token, -1) === '%') {
                if ($taxonomy = $this->taxonomyManager->getTaxonomy(substr($token, 1, -1))) {
                    if ($term = $post->getParentTerm($taxonomy->getTaxonomyType())) {
                        $objects[$taxonomy->getTaxonomyType()] = $term;
                    }
                }
            }
            else if (strlen($token) > 1 && substr($token, 0, 1) !== '.') {
                $parent = $this->factory->create('Post')->setPostType('page')->load($token, 'post_name');

                if ($parent->getId()) {
                $objects['parent_post_' . $parent->getId()] = $parent;
                }
            }
        }

        if ($this->isHierarchical()) {
            $parent = $post;
            $buffer = [];

            while(($parent = $parent->getParentPost()) !== false) {
            $buffer['parent_post_' . $parent->getId()] = $parent;
            }

            $objects = $objects + array_reverse($buffer);
        }

        return $objects ? $objects : false;
    }

    /**
     *
     *
     * @return array
     */
    public function getArchiveBreadcrumbStructure()
    {
        $crumbs = [];

        if ($this->withFront() && ($front = $this->getFront())) {
            $crumbs['front'] = [
                'label' => ucwords($front),
                'link'  => $this->url->getUrl($front),
            ];
        }

        $crumbs['post_type'] = $this;

        return $crumbs;
    }

    /**
     * Generate an array of URI's based on $results
     *
     * @param array $results
     * @return array
     */
    public static function generateRoutesFromArray($results, $prefix = '')
    {
        $objects = array();
        $byParent = array();

        foreach($results as $key => $result) {
            if (!$result['parent']) {
                $objects[$result['id']] = $result;
            }
            else {
                if (!isset($byParent[$result['parent']])) {
                    $byParent[$result['parent']] = array();
                }

                $byParent[$result['parent']][$result['id']] = $result;
            }
        }

        if (count($objects) === 0) {
            return false;
        }

        $routes = array();

        foreach($objects as $objectId => $object) {
            if (($children = self::createArrayTree($objectId, $byParent)) !== false) {
                $objects[$objectId]['children'] = $children;
            }

            $routes += self::createLookupTable($objects[$objectId], $prefix);
        }

        return $routes;
    }

    /**
     * Create a lookup table from an array tree
     *
     * @param array $node
     * @param string $idField
     * @param string $field
     * @param string $prefix = ''
     * @return array
     */
    protected static function createLookupTable(&$node, $prefix = '')
    {
        if (!isset($node['id'])) {
            return array();
        }

        $urls = array(
            $node['id'] => ltrim($prefix . '/' . urldecode($node['url_key']), '/')
        );

        if (isset($node['children'])) {
            foreach($node['children'] as $childId => $child) {
                $urls += self::createLookupTable($child, $urls[$node['id']]);
            }
        }

        return $urls;
    }

    /**
     * Create an array tree. This is used for creating static URL lookup tables
     * for categories and pages
     *
     * @param int $id
     * @param array $pool
     * @param string $field = 'parent'
     * @return false|array
     */
    protected static function createArrayTree($id, &$pool)
    {
        if (isset($pool[$id]) && $pool[$id]) {
            $children = $pool[$id];

            unset($pool[$id]);

            foreach($children as $childId => $child) {
                unset($children[$childId]['parent']);
                if (($result = self::createArrayTree($childId, $pool)) !== false) {
                    $children[$childId]['children'] = $result;
                }
            }

            return $children;
        }

        return false;
    }

    /**
     *
     *
     */
    public function load($modelId, $field = null)
    {
        return $this->factory->create('PostTypeManager')->getPostType($modelId);
    }
}
