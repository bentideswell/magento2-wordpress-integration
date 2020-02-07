<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\AbstractModel;

use \FishPig\WordPress\Api\Data\Entity\ViewableInterface;

class Term extends AbstractModel implements ViewableInterface
{
    /**
     *
     */
    const ENTITY = 'wordpress_term';

    /**
     * @const string
     */
    const CACHE_TAG = 'wordpress_term';

    /**
     * Event data
     *
     * @var string
     */
    protected $_eventPrefix = 'wordpress_term';
    protected $_eventObject = 'term';

    /**
     *
     *
     * @return 
     */
    public function _construct()
    {
    $this->_init('FishPig\WordPress\Model\ResourceModel\Term');
    }

    /**
     *
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     *
     *
     * @return string
     */
    public function getContent()
    {
        return $this->_getData('description');
    }

    /**
     * Get the taxonomy object for this term
     *
     * @return \FishPig\WordPress\Model\Term\Taxonomy
     */
    public function getTaxonomyInstance()
    {
        return $this->getTaxonomy() ? $this->taxonomyManager->getTaxonomy($this->getTaxonomy()) : false;
    }

    /**
     * Retrieve the taxonomy label
     *
     * @return string
     */
    public function getTaxonomyLabel()
    {
        if ($this->getTaxonomy()) {
            return ucwords(str_replace('_', ' ', $this->getTaxonomy()));
        }

        return false;
    }

    /**
     * Retrieve the parent term
     *
     * @reurn false|\FishPig\WordPress\Model\Term
     */
    public function getParentTerm()
    {
        if (!$this->hasParentTerm()) {
            $this->setParentTerm(false);

            if ($this->getParentId()) {
                $parentTerm = clone $this;

                $parentTerm->clearInstance()->load($this->getParentId());

                if ($parentTerm->getId()) {
                    $this->setParentTerm($parentTerm);
                }
            }
        }

        return $this->_getData('parent_term');
    }

    /**
     * Retrieve a collection of children terms
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Term\Collection
     */
    public function getChildrenTerms()
    {
        return $this->getCollection()->addParentFilter($this);
    }

    /**
     * Loads the posts belonging to this category
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */    
    public function getPostCollection()
    {
        return parent::getPostCollection()
            ->addIsViewableFilter()
            ->addTermIdFilter($this->getChildIds(), $this->getTaxonomy());
    }

    /**
     * Retrieve the numbers of items that belong to this term
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->getCount();
    }

    /**
     * Retrieve the parent ID
     *
     * @return int|false
     */    
    public function getParentId()
    {
        return $this->_getData('parent') ? $this->_getData('parent') : false;
    }

    /**
     * Retrieve the taxonomy type for this term
     *
     * @return string
     */
    public function getTaxonomyType()
    {
        return $this->getTaxonomy();
    }

    /**
     * Retrieve the URL for this term
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url->getUrl($this->getUri() . '/');
    }

    /**
     * Retrieve the URL for this term
     *
     * @return string
     */
    public function getUri()
    {
        if (!$this->hasUri()) {
            if ($taxonomy = $this->getTaxonomyInstance()) {
                $this->setUri($taxonomy->getUriById($this->getId()));
            }
        }

        return $this->_getData('uri');
    }

    /**
     * Get the number of posts belonging to the term
     *
     * @return int
     */
    public function getPostCount()
    {
        return (int)$this->getCount();
    }

    /**
     * Get an array of all child ID's
     * This includes the ID's of children's children
     *
     * @return array
     */
    public function getChildIds()
    {
        if (!$this->hasChildIds()) {
            $this->setChildIds(
                $this->getResource()->getChildIds($this->getId())
            );
        }

        return $this->_getData('child_ids');
    }

    /**
     * Get the meta value using ACF if it's installed
     *
     * @param string $key
     * @return mixed
     */
    public function getMetaValue($key)
    {
        return null;
    }
}
