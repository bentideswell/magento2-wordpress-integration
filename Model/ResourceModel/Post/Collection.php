<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Model\ResourceModel\Post;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Meta\Collection\AbstractCollection
{

	/**
	 * Name prefix of events that are dispatched by model
	 *
	 * @var string
	*/
	protected $_eventPrefix = 'wordpress_post_collection';
	
	/**
	 * Name of event parameter
	 *
	 * @var string
	*/
	protected $_eventObject = 'posts';

	/**
	 * True if term tables have been joined
	 * This stops the term tables being joined repeatedly
	 *
	 * @var array()
	 */
	protected $_termTablesJoined = array();

	/**
	 * Store post types to be allowed in collection
	 *
	 * @var array
	 */
	protected $_postTypes = array();
		
	/**
	 * Set the resource
	 *
	 * @return void
	 */
	public function _construct()
	{
        $this->_init('FishPig\WordPress\Model\Post', 'FishPig\WordPress\Model\ResourceModel\Post');
		
		$this->_map['fields']['ID']   = 'main_table.ID';		
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
				if ($postTypes = $this->_app->getPostTypes()) {
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

		if (count($this->_postTypes) === 1) {
			if ($this->_postTypes[0] === '*') {
				$this->_postTypes = array();
			}
		}

		if (count($this->_postTypes) === 0) {
			$this->addFieldToFilter('post_type', array('in' => array_keys($this->_app->getPostTypes())));
		}
		else {
			$this->addFieldToFilter('post_type', array('in' => $this->_postTypes));
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
		if (($sticky = trim($this->_app->getConfig()->getOption('sticky_posts'))) !== '') {
			$stickyIds = unserialize($sticky);
			
			if (count($stickyIds) > 0) {
				$select = $this->getConnection()
					->select()
					->from($this->getTable('wordpress_post'), array('value' => new \Zend_Db_Expr(1)))
					->where('main_table.ID IN (?)', $stickyIds)
					->limit(1);
				
				$this->getSelect()
					->columns(array('is_sticky' => new \Zend_Db_Expr('(' . $select . ')')))
					->order('is_sticky DESC');
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
		if (($sticky = trim($this->_app->getConfig()->getOption('sticky_posts'))) !== '') {
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

		$this->_postTypes = array_values(array_merge($this->_postTypes, (array)$postTypes));
		
		return $this;
	}
	
	/**
	 * Determine whether any post type filters exist
	 *
	 * @return bool
	 */
	public function hasPostTypeFilter()
	{
		return count($this->_postTypes) > 0;
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

    if ($this->_app->getConfig()->isLoggedIn()) {
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
			foreach($words as $word) {
				$conditions = array();

				foreach($fields as $key => $field) {
					$conditions[] = $this->getConnection()->quoteInto('`main_table`.`' . $field . '` LIKE ?', '%' . $word . '%');
				}

				$this->getSelect()->where(join(' ' . \Zend_Db_Select::SQL_OR . ' ', $conditions));
			}
			
			$this->addFieldToFilter('post_password', '');
		}
		else {
			$this->getSelect()->where('1=2');
		}

		return $this;
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
			$tableTermRel	 = $this->getTable('wordpress_term_relationship');
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
	**/
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
}
