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

class Term extends AbstractMetaModel implements PostCollectionGeneratorInterface, ViewableModelInterface
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
     * @var \FishPig\WordPress\Model\Term
     */
    private $parentTerm = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $wpContext->getPostCollectionFactory();
        $this->taxonomyRepository = $taxonomyRepository;
        $this->termRepository = $termRepository;
        parent::__construct($context, $registry, $wpContext, $metaDataProvider, $resource, $resourceCollection, $data);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_getData('name');
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        $taxonomy = $this->getTaxonomyInstance();
        $urlPath = $taxonomy->getUriById($this->getId());

        // $urlPath will include front if term is configured to use it
        // So we don't need to call getHomeUrlWithFront
        return $this->url->getHomeUrl($urlPath);
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
     * @reurn \FishPig\WordPress\Model\Term|false
     */
    public function getParentTerm()
    {
        if ($this->parentTerm === null) {
            $this->parentTerm = false;
            if ($parentId = $this->getParentId()) {
                try {
                    $this->parentTerm = $this->termRepository->get($parentId);
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $this->parentTerm = false;
                }
            }
        }
        
        return $this->parentTerm;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Term\Collection
     */
    public function getChildrenTerms(): \FishPig\WordPress\Model\ResourceModel\Term\Collection
    {
        return $this->getCollection()->addParentFilter($this);
    }

    /**
     * @return int
     */
    public function getParentId(): int
    {
        return (int)$this->_getData('parent');
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
     * Get a recursive array of all children IDs
     *
     * @return array
     */
    public function getChildIds(): array
    {
        return $this->getResource()->getChildIds($this->getId());
    }

    /**
     * @deprecated since 3.0
     * @return string
     */
    public function getTaxonomyLabel()
    {
        return $this->getName();
    }
    
    /**
     * @deprecated since 3.0
     * @return string
     */
    public function getTaxonomyType()
    {
        return $this->getTaxonomy();
    }
    
    /**
     * @deprecated since 3.0
     * @return int
     */
    public function getItemCount(): int
    {
        return (int)$this->getCount();
    }
}
