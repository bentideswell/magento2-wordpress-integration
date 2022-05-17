<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Post;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_post_collection';
    protected $_eventObject = 'posts';

    /**
     * @var array()
     */
    protected $_termTablesJoined = [];

    /**
     * @var array
     */
    protected $postTypes = [];

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \FishPig\WordPress\Model\ResourceModel\Post\Permalink $permalinkResource,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        string $modelName = null
    ) {
        $this->postTypeRepository = $postTypeRepository;
        $this->permalinkResource = $permalinkResource;
        $this->optionRepository = $optionRepository;
        $this->customerSession = $customerSession;
        $this->serializer = $serializer;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource,
            $modelName
        );
    }
    
    /**
     *
     */
    public function _construct()
    {
        $this->_map['fields']['ID'] = 'main_table.ID';
        $this->_map['fields']['post_type'] = 'main_table.post_type';
        $this->_map['fields']['post_status'] = 'main_table.post_status';

        return parent::_construct();
    }

    /**
     *
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->setOrder('main_table.menu_order', 'ASC');
        $this->setOrder('main_table.post_date', 'DESC');
            
        return $this;
    }

    /**
     * Add the permalink data before loading the collection
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        /*
        if (!$this->hasPostTypeFilter()) {
            if ($this->getFlag('source') instanceof \FishPig\WordPress\Model\Term) {
                if ($postTypes = $this->postTypeRepository->getAll()) {
                    $supportedTypes = [];

                    foreach ($postTypes as $postType) {
                        if ($postType->isTaxonomySupported($this->getFlag('source')->getTaxonomy())) {
                            $supportedTypes[] = $postType->getPostType();
                        }
                    }

                    $this->addPostTypeFilter($supportedTypes);
                }
            }
        }*/

        if (count($this->postTypes) === 1) {
            if ($this->postTypes[0] === '*') {
                $this->postTypes = [];
            }
        }

        if (count($this->postTypes) === 0) {
            $this->postTypes = array_keys($this->postTypeRepository->getAll());
        }

        $this->addFieldToFilter('post_type', ['in' => $this->postTypes]);

        if (!$this->getFlag('skip_permalink_generation')) {
            if ($sql = $this->permalinkResource->getPermalinkSqlColumn($this->postTypes)) {
                $this->getSelect()->columns(['permalink' => $sql]);
            }
        }
        
        return $this;
    }

    /**
     * Ensure that is any pages are in the collection, they are correctly cast
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();

        $this->getResource()->preparePosts($this->_items);

        if ($stickyIds = $this->getFlag('_sticky_ids')) {
            foreach ($this->_items as $item) {
                $item->setData('is_sticky', in_array($item->getId(), $stickyIds));
            }
        }

        return $this;
    }

    /**
     * @param  int $userId
     * @return self
     */
    public function addUserIdFilter(int $userId): self
    {
        return $this->addFieldToFilter('post_author', $userId);
    }

    /**
     * Filters the collection with an archive date
     * EG: 2010/10
     *
     * @param string $archiveDate
     */
    public function addArchiveDateFilter($archiveDate, $isDaily = false)
    {
        if ($isDaily) {
            $this->getSelect()->where("main_table.post_date LIKE ?", str_replace("/", "-", $archiveDate)." %");
        } else {
            $this->getSelect()->where("main_table.post_date LIKE ?", str_replace("/", "-", $archiveDate)."-%");
        }

        return $this;
    }

    /**
     * Add the is_sticky field to the posts.
     * This returns all posts but sticky posts will be at the start of the collection
     * To only return sticky posts, see self::addIsStickyPostFilter
     *
     * @return $this
     */
    public function addStickyPostsToCollection()
    {
        if (($sticky = trim($this->optionRepository->get('sticky_posts'))) !== '') {
            $stickyIds = $this->serializer->unserialize($sticky);

            if (count($stickyIds) > 0) {
                $this->setFlag('_sticky_ids', $stickyIds);
                if ($orders = $this->getSelect()->getPart('order')) {
                    $this->getSelect()->reset('order');
                }

                $this->getSelect()->order('FIELD(main_table.ID, ' . implode(', ', $stickyIds) . ') DESC');

                if ($orders) {
                    foreach ($orders as $order) {
                        $this->getSelect()->order(implode(' ', $order));
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Filter the collection so that only sticky posts are returned
     *
     * @param  bool $flag = true
     * @return $this
     */
    public function addIsStickyPostFilter($flag = true)
    {
        if (($sticky = trim($this->optionRepository->get('sticky_posts'))) !== '') {
            $stickyIds = $this->serializer->unserialize($sticky);

            if (count($stickyIds) > 0) {
                $this->setFlag('_sticky_ids', $stickyIds);
                $this->getSelect()->where('ID ' . ($flag ? 'IN' : ' NOT IN') . ' (?)', $stickyIds);
            }
        }

        return $this;
    }

    /**
     * Add a post type filter to the collection
     *
     * @param  string|array $postTypes
     * @return $this
     */
    public function addPostTypeFilter($postTypes)
    {
        if (!is_array($postTypes) && strpos($postTypes, ',') !== false) {
            $postTypes = explode(',', $postTypes);
        }

        $this->postTypes = array_values(array_merge($this->postTypes, (array)$postTypes));

        return $this;
    }

    /**
     * Determine whether any post type filters exist
     *
     * @return bool
     */
    public function hasPostTypeFilter()
    {
        return count($this->postTypes) > 0;
    }

    /**
     * Adds a published filter to collection
     */
    public function addIsPublishedFilter()
    {
        return $this->addIsViewableFilter();
    }

    /**
     * Filters the collection so that only posts that can be viewed are displayed
     *
     * @return $this
     */
    public function addIsViewableFilter()
    {
        $fields = ['publish', 'protected'];

        if ($this->customerSession->isLoggedIn()) {
            $fields[] = 'private';
        }

        return $this->addStatusFilter($fields);
    }

    /**
     * Adds a filter to the status column
     *
     * @param string $status
     */
    public function addStatusFilter($status)
    {
        $op = is_array($status) ? 'in' : 'eq';

        return $this->addFieldToFilter('post_status', [$op => $status]);
    }

    /**
     * Orders the collection by post date
     *
     * @param string $dir
     */
    public function setOrderByPostDate($dir = 'desc')
    {
        $this->_orders = [];

        return $this->setOrder('post_date', $dir);
    }

    /**
     * Filter the collection by a date
     *
     * @param string $dateStr
     */
    public function addPostDateFilter($dateStr)
    {
        if (!is_array($dateStr) && strpos($dateStr, '%') !== false) {
            $this->addFieldToFilter('post_date', ['like' => $dateStr]);
        } else {
            $this->addFieldToFilter('post_date', $dateStr);
        }

        return $this;
    }

    /**
     * Skip the permalink generation
     *
     * @return $this
     */
    public function removePermalinkFromSelect()
    {
        return $this->setFlag('skip_permalink_generation', true);
    }

    /**
     * Filters the collection by an array of words on the array of fields
     *
     * @param array  $words    - words to search for
     * @param array  $fields   - fields to search
     * @param string $operator
     */
    public function addSearchStringFilter(array $words, array $fields)
    {
        if (count($words) > 0) {
            $db = $this->getConnection();

            // Set word as key and weight (value) to 1
            $words = array_fill_keys(array_unique($words), 1);

            // Ensure main query only contains correct posts
            foreach ($words as $word => $wordWeight) {
                $conditions = [];

                foreach ($fields as $field => $fieldWeight) {
                    $conditions[] = $db->quoteInto(
                        'main_table.' . $field . ' LIKE ?',
                        '%' . $this->_escapeSearchString($word) . '%'
                    );
                }

                $this->getSelect()->where(join(' OR ', $conditions));
            }

            if (count($words) > 1) {
                // Add full word into words array with higher weight
                $words = [implode(' ', array_keys($words)) => 5] + $words;
            }

            // Calculate weight
            $weightSql = [];

            foreach ($words as $word => $wordWeight) {
                foreach ($fields as $field => $fieldWeight) {
                    $weightSql[] = $db->quoteInto(
                        'IF (main_table.' . $field . ' LIKE ?, ' . ($wordWeight + $fieldWeight) . ', 0)',
                        '%' . $this->_escapeSearchString($word) . '%'
                    );
                }
            }

            $expression = new \Zend_Db_Expr('(' . implode(' + ', $weightSql) . ')');

            // Add Weight column to query
            $this->getSelect()->columns(['weight' => $expression]);

            // Reset order then add order by weight
            $this->getSelect()->reset('order')->order('weight DESC');

            // Ensure password protected posts aren't included
            $this->addFieldToFilter('post_password', '');
        } else {
            // Empty search so force no results
            $this->getSelect()->where('1=2');
        }

        return $this;
    }

    /**
     * Fix search issue when searching for: "%FF%FE"
     *
     * @param  string
     * @return string
     */
    protected function _escapeSearchString($s)
    {
        // phpcs:ignore -- todo
        return htmlspecialchars($s);
    }

    /**
     * Filters the collection by a term ID and type
     *
     * @param int|array $termId
     * @param string    $type
     */
    public function addTermIdFilter($termId, $type)
    {
        $this->joinTermTables($type);

        if (is_array($termId)) {
            $this->getSelect()->where("tax_{$type}.term_id IN (?)", $termId);
        } else {
            $this->getSelect()->where("tax_{$type}.term_id = ?", $termId);
        }

        return $this;
    }

    /**
     * Filters the collection by a term and type
     *
     * @param int|array $termId
     * @param string    $type
     */
    public function addTermFilter($term, $type, $field = 'slug')
    {
        $this->joinTermTables($type);

        if (is_array($term)) {
            $this->getSelect()->where("terms_{$type}.{$field} IN (?)", $term);
        } else {
            $this->getSelect()->where("terms_{$type}.{$field} = ?", $term);
        }

        return $this;
    }

    /**
     * Joins the category tables to the collection
     * This allows filtering by category
     */
    public function joinTermTables($type)
    {
        $type = strtolower(trim($type));

        if (!isset($this->_termTablesJoined[$type])) {
            $tableTax = $this->getTable('wordpress_term_taxonomy');
            $tableTermRel     = $this->getTable('wordpress_term_relationship');
            $tableTerms = $this->getTable('wordpress_term');

            $this->getSelect()->join(['rel_' . $type => $tableTermRel], "rel_{$type}.object_id=main_table.ID", '')
                ->join(
                    [
                        'tax_' . $type => $tableTax
                    ],
                    "tax_{$type}.term_taxonomy_id=rel_{$type}.term_taxonomy_id AND tax_{$type}.taxonomy='{$type}'",
                    null
                )
                ->join(['terms_' . $type => $tableTerms], "terms_{$type}.term_id = tax_{$type}.term_id", '')
                ->distinct();

            $this->_termTablesJoined[$type] = true;
        }

        return $this;
    }

    /**
     * Add post parent ID filter
     *
     * @param int $postParentId
     */
    public function addPostParentIdFilter($postParentId)
    {
        $this->getSelect()->where("main_table.post_parent=?", $postParentId);

        return $this;
    }

    /**
     * Ensure correct size is calculated
     *
     * @return int
     */
    public function getSize()
    {
        if ($this->_totalRecords === null) {
            $this->_renderFilters();

            $countSelect = clone $this->getSelect();
            $countSelect->reset(\Magento\Framework\DB\Select::ORDER);
            $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
            $countSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
            $countSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

            $countSelect->columns(new \Zend_Db_Expr('main_table.ID'));

            $this->_totalRecords = count($this->getConnection()->fetchCol($countSelect));
        }

        return (int)$this->_totalRecords;
    }

    /**
     * Order the collection by the menu order field
     *
     * @param  string $dir
     * @return
     */
    public function setOrderByMenuOrder($dir = 'asc')
    {
        $this->getSelect()->order('menu_order ' . $dir);

        return $this;
    }
    
    /**
     * @param  int $categoryId
     * @return self
     */
    public function addCategoryIdFilter($categoryId): self
    {
        return $this->addTermIdFilter($categoryId, 'category');
    }

    /**
     * @param  int $categoryId
     * @return self
     */
    public function addTagIdFilter($tagId): self
    {
        return $this->addTermIdFilter($tagId, 'post_tag');
    }
}
