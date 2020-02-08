<?php
/**
 *
 */
namespace FishPig\WordPress\Model\ResourceModel\Post;

use FishPig\WordPress\Model\ResourceModel\Meta\Collection\AbstractCollection as AbstractMetaCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use FishPig\WordPress\Model\OptionManager;
use FishPig\WordPress\Model\PostTypeManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Collection extends AbstractMetaCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_post_collection';

    /**
     * @var string
     */
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
     * @return void
     */
    public function _construct()
    {
        $this->_init('FishPig\WordPress\Model\Post', 'FishPig\WordPress\Model\ResourceModel\Post');

        $this->_map['fields']['ID'] = 'main_table.ID';
        $this->_map['fields']['post_type'] = 'main_table.post_type';
        $this->_map['fields']['post_status'] = 'main_table.post_status';

        return parent::_construct();
    }

    /**
     * Init collection select
     *
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
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

        if (!$this->getFlag('skip_permalink_generation')) {
            if ($sql = $this->getResource()->getPermalinkSqlColumn()) {
                $this->getSelect()->columns(array('permalink' => $sql));
            }
        }

        if (!$this->hasPostTypeFilter()) {
            if ($this->getFlag('source') instanceof \FishPig\WordPress\Model\Term) {
                if ($postTypes = $this->postTypeManager->getPostTypes()) {
                    $supportedTypes = array();

                    foreach($postTypes as $postType) {
                        if ($postType->isTaxonomySupported($this->getFlag('source')->getTaxonomy())) {
                            $supportedTypes[] = $postType->getPostType();
                        }
                    }

                    $this->addPostTypeFilter($supportedTypes);
                }
            }
        }

        if (count($this->postTypes) === 1) {
            if ($this->postTypes[0] === '*') {
                $this->postTypes = array();
            }
        }

        if (count($this->postTypes) === 0) {
            $this->addFieldToFilter('post_type', array('in' => array_keys($this->postTypeManager->getPostTypes())));
        }
        else {
            $this->addFieldToFilter('post_type', array('in' => $this->postTypes));
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

        return $this;
    }

    /**
     * Filters the collection by an array of post ID's and category ID's
     * When filtering by a category ID, all posts from that category will be returned
     * If you change the param $operator to AND, only posts that are in a category specified in
     * $categoryIds and $postIds will be returned
     *
     * @param mixed $postIds
     * @param mixed $categoryIds
     * @param string $operator
     */
    public function addCategoryAndPostIdFilter($postIds, $categoryIds, $operator = 'OR')
    {
        if (!is_array($postIds)) {
            $postIds = array($postIds);
        }

        if (!is_array($categoryIds)) {
            $categoryIds = array($categoryIds);
        }

        if (count($categoryIds) > 0) {
            $this->joinTermTables('category');
        }

        $readAdapter = $this->getConnection();

        $postSql = $readAdapter->quoteInto("`main_table`.`ID` IN (?)", $postIds);
        $categorySql = $readAdapter->quoteInto("`tax_category`.`term_id` IN (?)", $categoryIds);

        if (count($postIds) > 0 && count($categoryIds) > 0) {
            $this->getSelect()->where("{$postSql} {$operator} {$categorySql}");
        }
        else if (count($postIds) > 0) {
            $this->getSelect()->where("{$postSql}");
        }
        else if (count($categoryIds) > 0) {
            $this->getSelect()->where("{$categorySql}");
        }

        return $this;
    }

    /**
     * Filter the collection by a category ID
     *
     * @param int $categoryId
     * @return $this
     */
    public function addCategoryIdFilter($categoryId)
    {
        return $this->addTermIdFilter($categoryId, 'category');
    }

    /**
     * Filter the collection by a tag ID
     *
     * @param int $categoryId
     * @return $this
     */
    public function addTagIdFilter($tagId)
    {
        return $this->addTermIdFilter($tagId, 'post_tag');
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
            $this->getSelect()->where("`main_table`.`post_date` LIKE ?", str_replace("/", "-", $archiveDate)." %");
        }
        else {
            $this->getSelect()->where("`main_table`.`post_date` LIKE ?", str_replace("/", "-", $archiveDate)."-%");
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
        if (($sticky = trim($this->optionManager->getOption('sticky_posts'))) !== '') {
            $stickyIds = unserialize($sticky);

            if (count($stickyIds) > 0) {
                if ($orders = $this->getSelect()->getPart('order')) {
                    $this->getSelect()->reset('order');
                }

                $this->getSelect()->order('FIELD(main_table.ID, ' . implode(', ', $stickyIds) . ') DESC');

                if ($orders) {
                    foreach($orders as $order) {
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
     * @param bool $flag = true
     * @return $this
     */
    public function addIsStickyPostFilter($flag = true)
    {
        if (($sticky = trim($this->optionManager->getOption('sticky_posts'))) !== '') {
            $stickyIds = unserialize($sticky);

            if (count($stickyIds) > 0) {
                $this->getSelect()->where('ID ' . ($flag ? 'IN' : ' NOT IN') . ' (?)', $stickyIds);
            }
        }

        return $this;
    }

    /**
     * Add a post type filter to the collection
     *
     * @param string|array $postTypes
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
     *
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

        if ($this->wpContext->getCustomerSession()->isLoggedIn()) {
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

        return $this->addFieldToFilter('post_status', array($op => $status));
    }

    /**
     * Orders the collection by post date
     *
     * @param string $dir
     */
    public function setOrderByPostDate($dir = 'desc')
    {
        $this->_orders = array();

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
            $this->addFieldToFilter('post_date', array('like' => $dateStr));
        }
        else {
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
     * @param array $words - words to search for
     * @param array $fields - fields to search
     * @param string $operator
     */
    public function addSearchStringFilter(array $words, array $fields)
    {
        if (count($words) > 0) {
            $db = $this->getConnection();

            // Set word as key and weight (value) to 1
            $words = array_fill_keys(array_unique($words), 1);

            // Ensure main query only contains correct posts
            foreach($words as $word => $wordWeight) {
                $conditions = array();

                foreach($fields as $field => $fieldWeight) {
                    $conditions[] = $db->quoteInto('`main_table`.`' . $field . '` LIKE ?', '%' . $this->_escapeSearchString($word) . '%');
                }

                $this->getSelect()->where(join(' OR ', $conditions));
            }

            if (count($words) > 1) {
                // Add full word into words array with higher weight
                $words = array(implode(' ', array_keys($words)) => 5) + $words;
            }

            // Calculate weight
            $weightSql = array();

            foreach($words as $word => $wordWeight) {
                foreach($fields as $field => $fieldWeight) {
                    $weightSql[] = $db->quoteInto(
                        'IF (`main_table`.`' . $field . '` LIKE ?, ' . ($wordWeight + $fieldWeight) . ', 0)', '%' . $this->_escapeSearchString($word) . '%'
                    );
                }
            }

            $expression = new \Zend_Db_Expr('(' . implode(' + ', $weightSql) . ')');

            // Add Weight column to query
            $this->getSelect()->columns(array('weight' => $expression));

            // Reset order then add order by weight
            $this->getSelect()->reset('order')->order('weight DESC');

            // Ensure password protected posts aren't included
            $this->addFieldToFilter('post_password', '');
        }
        else {
            // Empty search so force no results
            $this->getSelect()->where('1=2');
        }

        return $this;
    }

    /**
     * Fix search issue when searching for: "%FF%FE"
     *
     * @param string
     * @return string
     */
    protected function _escapeSearchString($s)
    {
        return htmlspecialchars($s);
    }

    /**
     * Filters the collection by a term ID and type
     *
     * @param int|array $termId
     * @param string $type
     */
    public function addTermIdFilter($termId, $type)
    {
        $this->joinTermTables($type);

        if (is_array($termId)) {
            $this->getSelect()->where("`tax_{$type}`.`term_id` IN (?)", $termId);
        }
        else {
            $this->getSelect()->where("`tax_{$type}`.`term_id` = ?", $termId);
        }

        return $this;
    }

    /**
     * Filters the collection by a term and type
     *
     * @param int|array $termId
     * @param string $type
     */
    public function addTermFilter($term, $type, $field = 'slug')
    {
        $this->joinTermTables($type);

        if (is_array($term)) {
            $this->getSelect()->where("`terms_{$type}`.`{$field}` IN (?)", $term);
        }
        else {
            $this->getSelect()->where("`terms_{$type}`.`{$field}` = ?", $term);
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

            $this->getSelect()->join(array('rel_' . $type => $tableTermRel), "`rel_{$type}`.`object_id`=`main_table`.`ID`", '')
                ->join(array('tax_' . $type => $tableTax), "`tax_{$type}`.`term_taxonomy_id`=`rel_{$type}`.`term_taxonomy_id` AND `tax_{$type}`.`taxonomy`='{$type}'", '')
                ->join(array('terms_' . $type => $tableTerms), "`terms_{$type}`.`term_id` = `tax_{$type}`.`term_id`", '')
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

        return intval($this->_totalRecords);
    }

    /**
     * Order the collection by the menu order field
     *
     * @param string $dir
     * @return
     */
    public function setOrderByMenuOrder($dir = 'asc')
    {
        $this->getSelect()->order('menu_order ' . $dir);

        return $this;
    }
}
