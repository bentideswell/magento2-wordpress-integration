<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Taxonomy extends \Magento\Framework\DataObject
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_taxonomy';
    const CACHE_TAG = 'wordpress_taxonomy';

    /**
     * @var \FishPig\WordPress\Model\ResourceModel\Taxonomy
     */
    private $_resource;
    
    /**
     * @param array $data = []
     */
    public function __construct(
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Taxonomy $resource,
        array $data = []
    ) {
        $this->url = $wpContext->getUrl();
        $this->_resource = $resource;
        
        parent::__construct($data);
    }
    
    /**
     * Get the URI's that apply to $uri
     *
     * @param  string $uri = ''
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
        return $this->getResource()->getAllRoutes($this);
    }


    /**
     * Retrieve the URI for $term
     *
     * @param  \FishPig\WordPress\Model\Term $term
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
     * @return bool
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
     * @param  string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        if (!isset($this->_data['rewrite'])) {
            $this->_data['rewrite'] = [];
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
     * @param  \FishPig\WordPress\Model\Post $post
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostTermsCollection(\FishPig\WordPress\Model\Post $post)
    {
        /* ToDo */
        return $this->factory->create('FishPig\WordPress\Model\ResourceModel\Term\Collection')
            ->addTaxonomyFilter($this->getTaxonomyType())
            ->addPostIdFilter($post->getId());
    }

    /**
     * @return string
     */
    public function getTaxonomyType()
    {
        return $this->getData('taxonomy_type') ? $this->getData('taxonomy_type') : $this->getData('name');
    }

    /**
     * @return string
     */
    public function getTaxonomy(): string
    {
        return $this->getTaxonomyType();
    }
    
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Taxonomy
     */
    public function getResource(): \FishPig\WordPress\Model\ResourceModel\Taxonomy
    {
        return $this->_resource;
    }
}
