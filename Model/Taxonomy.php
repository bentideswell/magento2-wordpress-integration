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
     * @return string
     */
    public function getName()
    {
        return $this->getData('labels/name');
    }

    /**
     * @return string
     */
    public function getSingularName()
    {
        return $this->getData('labels/singular_name') ?: $this->getName();
    }
    
    /**
     * @return bool
     */
    public function isHierarchical(): bool
    {
        return (int)$this->getData('hierarchical') === 1;
    }

    /**
     * @return bool
     */
    public function isRewriteHierarchical(): bool
    {
        return $this->isHierarchical() && (int)$this->getData('rewrite/hierarchical') === 1;
    }

    /**
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
     * @return bool
     */
    public function withFront()
    {
        return (int)$this->getData('rewrite/with_front') === 1;
    }
    
    /**
     * @return array
     */
    public function getAllRoutes()
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
        if (($uris = $this->getAllRoutes()) !== false) {
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
     * @return string
     */
    public function getTaxonomy(): string
    {
        return $this->getData('taxonomy');
    }
    
    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Taxonomy
     */
    public function getResource(): \FishPig\WordPress\Model\ResourceModel\Taxonomy
    {
        return $this->_resource;
    }
    
    /**
     * Convert the object to a string and return the taxonomy type code
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getTaxonomy();
    }

    /**
     * @deprecated since 3.0
     */
    public function getTaxonomyType()
    {
        return $this->getTaxonomy();
    }

    /**
     * @deprecated since 3.0
     */
    public function getUris($uri = '')
    {
        return $this->getResource()->getAllRoutes();
    }

    /**
     * @deprecated since 3.0
     */
    public function getAllUris()
    {
        return $this->getResource()->getAllRoutes();
    }
}
