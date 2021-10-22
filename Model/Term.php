<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Api\Data\PostCollectionGeneratorInterface;
use FishPig\WordPress\Api\Data\ViewableModelInterface;

class Term extends AbstractModel implements PostCollectionGeneratorInterface, ViewableModelInterface
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_term';
    const CACHE_TAG = 'wordpress_term';

    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_term';
    protected $_eventObject = 'term';

    /**
     * @var \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory
     */
    private $postCollectionFactory;
    
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->taxonomyRepository = $taxonomyRepository;
        parent::__construct($context, $registry, $wpContext, $resource, $resourceCollection, $data);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection(): \FishPig\WordPress\Model\ResourceModel\Post\Collection
    {
        return $this->postCollectionFactory->create()->addTermIdFilter(
            (int)$this->getId(),
            $this->getTaxonomy()         
        );
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->_getData('description');
    }
    
    /**
     * @return \FishPig\WordPress\Model\Taxonomy
     */
    public function getTaxonomyInstance(): \FishPig\WordPress\Model\Taxonomy
    {
        return $this->taxonomyRepository->get($this->getTaxonomy());
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
     * Retrieve the numbers of items that belong to this term
     *
     * @return int
     */
    public function getItemCount(): int
    {
        return (int)$this->getCount();
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
}
