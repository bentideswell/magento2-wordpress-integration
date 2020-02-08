<?php
/**
 *
 */
namespace FishPig\WordPress\Model\ResourceModel\Term;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_term_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'terms';

    /**
     *
     */
    public function _construct()
    {
        $this->_init('FishPig\WordPress\Model\Term', 'FishPig\WordPress\Model\ResourceModel\Term');
    }

    /**
     * Flag that determines whether term_relationships has been joined
     *
     * @var bool
     */
    protected $_relationshipTableJoined = false;

    /**
     * Ensures that only posts and not pages are returned
     * WP stores posts and pages in the same DB table
     *
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            array('taxonomy' => $this->getTable('wordpress_term_taxonomy')),
            '`main_table`.`term_id` = `taxonomy`.`term_id`',
            array('term_taxonomy_id', 'taxonomy', 'description', 'count', 'parent')
        );

        // Reverse the order. This then matches the WP order
        if ($this->getResource()->tableHasTermOrderField()) {
            $this->getSelect()->order('term_order ASC');
        }

        $this->getSelect()->order('term_id ASC');

        return $this;
    }

    /**
     * Set the collection by the name field
     *
     * @param string $dir = 'ASC'
     * @return $this
     */
    public function setOrderByName($dir = 'ASC')
    {
        $this->getSelect()
            ->reset(\Zend_Db_Select::ORDER)
            ->order('main_table.name ' . $dir);

        return $this;
    }

    /**
     * Add a slug filter to the collection
     *
     * @param string $slug
     * @return $this
     */
    public function addSlugFilter($slug)
    {
        return $this->addFieldToFilter('slug', $slug);
    }

    /**
     * Filter the collection by taxonomy
     *
     * @param string $taxonomy
     * @return $this
     */    
    public function addTaxonomyFilter($taxonomy)
    {
        return $this->addFieldToFilter('taxonomy', $taxonomy);
    }

    /**
     * Filter the collection on the parent field
     *
     * @param int|\FishPig\WordPress\Model\Term
     * @return $this
     */
    public function addParentFilter($parentId)
    {
        if (is_object($parentId)) {
            $parentId = $parentId->getId();
        }

        return $this->addFieldToFilter('parent', $parentId);
    }

    /**
     * See self::addParentFilter
     * This is kept in for backwards compatibility
     *
     * @return $this
     */
    public function addParentIdFilter($parentId)
    {
        return $this->addParentFilter($parentId);
    }

    /**
     * Join the relationship table
     * No values are added to the result, but this can be used to test whether term has
     * a particular object_id
     *
     * @return $this
     */
    protected function _joinRelationshipTable()
    {
        if ($this->_relationshipTableJoined === false) {
            $this->getSelect()
                ->distinct()
                ->joinLeft(
                    array('relationship' => $this->getTable('wordpress_term_relationship')),
                    '`taxonomy`.`term_taxonomy_id` = `relationship`.`term_taxonomy_id`',
                    ''
                );
        }

        return $this;
    }

    /**
     * Filter the collection by object ID
     * To pass in multiple object ID's, pass:
     *
     * @param int|array $objectId
     * @return $this
     */
    public function addObjectIdFilter($objectId)
    {
        if (is_array($objectId)) {
            $objectId = array('in' => $objectId);
        }

        return $this->_joinRelationshipTable()->addFieldToFilter('object_id', $objectId);
    }

    /**
     * See self::addObjectIdFilter
     *
     */
    public function addPostIdFilter($postId)
    {
        return $this->addObjectIdFilter($postId);
    }

    /**
     * Order the collection by the count field
     *
     * @param string $dir
     */
    public function addOrderByItemCount($dir = 'desc')
    {
        $this->getSelect()->order('taxonomy.count ' . $dir);

        return $this;
    }

    /**
     * Determine whether the term has objects associated with it
     *
     * @return $this
     */
    public function addHasObjectsFilter()
    {
        return $this->addFieldToFilter('count', array('gt' => 0));
    }

    /**
     * Filter the collection so that only tags in the cloud
     * are returned
     *
     */
    public function addCloudFilter($taxonomy)
    {
        $cloudIdsSelect = $this->wpContext->getFactory()->create('Term')->getCollection()
            ->addTaxonomyFilter($taxonomy)
            ->addOrderByItemCount()
            ->setPageSize(20)
            ->setCurPage(1)
                ->getSelect()
                    ->setPart('columns', array())
                    ->columns(array('main_table.term_id'));

        return $this->addTaxonomyFilter($taxonomy)->addFieldToFilter(
            'main_table.term_id', 
            array('in' => new \Zend_Db_Expr($cloudIdsSelect))
        );
    }
}
