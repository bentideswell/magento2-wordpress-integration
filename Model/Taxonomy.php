<?php
/**
 *
 */    
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\AbstractModel;
use FishPig\WordPress\Api\Data\Entity\ViewableInterface;
use FishPig\WordPress\Model\PostTypeManager;

class Taxonomy extends AbstractResourcelessModel/** implements ViewableInterface*/
{    
    /**
     * @const string
     */
    const ENTITY = 'wordpress_taxonomy';

    /**
     * @const string
     */
    const CACHE_TAG = 'wordpress_taxonomy';

    /**
     * Get the URI's that apply to $uri
     *
     * @param string $uri = ''
     * @return array|false
     */
    public function getUris($uri = '')
    {
        if ($uri && $this->getSlug() && strpos($uri, $this->getSlug()) === false) {
            return false;
        }

        return $this->getAllUris();
    }

    /**
     * Get all of the URI's for this taxonomy
     *
     * @return array|false
     */
    public function getAllUris()
    {
        if ($this->hasAllUris()) {
            return $this->_getData('all_uris');
        }

        $this->setAllUris(false);

        $resource   = $this->wpContext->resourceConnection;
        $connection = $resource->getConnection();

        $select = $connection->select()
            ->from(
                array(
                    'term' => $resource->getTable('wordpress_term')), 
                    array(
                        'id' => 'term_id', 
                        'url_key' => 'slug',
//                  'url_key' => new \Zend_Db_Expr("IF(parent=0,TRIM(LEADING '/' FROM CONCAT('" . rtrim($this->getSlug(), '/') . "/', slug)), slug)")
                    )
                )
                ->join(
                    array('tax' => $resource->getTable('wordpress_term_taxonomy')),
                    $connection->quoteInto("tax.term_id = term.term_id AND tax.taxonomy = ?", $this->getTaxonomyType()),
                    'parent'
                );

        if ($results = $connection->fetchAll($select)) {            
            if ((int)$this->getData('rewrite/hierarchical') === 1) {
                $this->setAllUris(PostType::generateRoutesFromArray($results, $this->getSlug()));
            }
            else {
                $routes = [];

                foreach($results as $result) {
                    $routes[$result['id']] = ltrim($this->getSlug() . '/' . $result['url_key'], '/');
                }

                $this->setAllUris($routes);
            }
        }

        return $this->_getData('all_uris');
    }

    /**
     * Retrieve the URI for $term
     *
     * @param \FishPig\WordPress\Model\Term $term
     * @return false|string
     */
    public function getUriById($id, $includePrefix = true)
    {
        if (($uris = $this->getAllUris()) !== false) {
            if (isset($uris[$id])) {
                $uri = $uris[$id];

                if (!$includePrefix && $this->getSlug() && strpos($uri, $this->getSlug() . '/') === 0) {
                    $uri = substr($uri, strlen($this->getSlug())+1);
                }

                return $uri;
            }
        }

        return false;
    }

    /**
     * Determine whether the taxonomy uses a hierarchy in it's link
     *
     * @return  bool
     */
    public function isHierarchical()
    {
        return (int)$this->getData('hierarchical') === 1;
    }

    /**
     * Get the taxonomy slug
     *
     * @return string
     */
    public function getSlug()
    {
        $slug = trim($this->getData('rewrite/slug'), '/');

        if ($this->withFront() && ($front = $this->url->getFront())) {
            $slug = rtrim($front . '/' . $slug, '/');
        }

        return $slug;
    }

    /**
     * Change the 'slug' value
     *
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        if (!isset($this->_data['rewrite'])) {
            $this->_data['rewrite'] = array();
        }

        $this->_data['rewrite']['slug'] = $slug;

        return $this;
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
     * Get a collection of terms that belong this taxonomy and $post
     *
     * @param \FishPig\WordPress\Model\Post $post
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostTermsCollection(\FishPig\WordPress\Model\Post $post)
    {
        return $this->factory->create('FishPig\WordPress\Model\ResourceModel\Term\Collection')
            ->addTaxonomyFilter($this->getTaxonomyType())
            ->addPostIdFilter($post->getId());
    }

    /**
     *
     *
     */
    public function getTaxonomyType()
    {
        return $this->getData('taxonomy_type') ? $this->getData('taxonomy_type') : $this->getData('name');
    }

    /**
     *
     *
     */
    public function getTaxonomy()
    {
        return $this->getTaxonomyType();
    }
}
