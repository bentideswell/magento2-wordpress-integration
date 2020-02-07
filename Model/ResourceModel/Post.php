<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

use FishPig\WordPress\Model\ResourceModel\Meta\AbstractMeta;
use Magento\Framework\Model\ResourceModel\Db\Context;
use FishPig\WordPress\Model\Context as WPContext;
use FishPig\WordPress\Model\PostTypeManager;
use FishPig\WordPress\Model\TaxonomyManager;

class Post extends AbstractMeta
{
    /**
     * @var
     */
    protected $postTypeManager;

    /**
     * @var 
     */
    protected $taxonomyManager;

    /**
     *
     *
     * @return
     */
    public function __construct(
       Context $context,
     WPContext $wpContext,
               $connectionName = null
    )
    {
        $this->postTypeManager = $wpContext->getPostTypeManager();
        $this->taxonomyManager = $wpContext->getTaxonomyManager();

        parent::__construct($context, $wpContext, $connectionName);
    }

    /**
     * Set the table and primary key
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('wordpress_post', 'ID');
    }

    /**
     * Custom load SQL
     *
     * @param string $field - field to match $value to
     * @param string|int $value - $value to load record based on
     * @param Mage_Core_Model_Abstract $object - object we're trying to load to
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = $this->getConnection()->select()
            ->from(array('e' => $this->getMainTable()))
            ->where("e.{$field}=?", $value)
            ->limit(1);

        $postType = $object->getPostType();

        if (!in_array($postType, array('*', ''))) {
            $select->where('e.post_type ' . (is_array($postType) ? 'IN' : '=') . ' (?)', $postType);
        }

        $select->columns(array('permalink' => $this->getPermalinkSqlColumn()));

        return $select;
    }

    /**
     *
     *
     */
    public function completePostSlug($slug, $postId, $postType)
    {
        if (!preg_match_all('/(\%[a-z0-9_-]{1,}\%)/U', $slug, $matches)) {
            return $slug;
        }

        $matchedTokens = $matches[0];

        foreach($matchedTokens as $mtoken) {
            if ($mtoken === '%postnames%') {
                $slug = str_replace($mtoken, $postType->getHierarchicalPostName($postId), $slug);
            }
            else if ($taxonomy = $this->taxonomyManager->getTaxonomy(trim($mtoken, '%'))) {
                $termData = $this->getParentTermsByPostId(array($postId), $taxonomy->getTaxonomyType(), false);

                foreach($termData as $key => $term) {
                    if ((int)$term['object_id'] === (int)$postId) {
                        $slug = str_replace($mtoken, $taxonomy->getUriById($term['term_id'], false), $slug);

                        break;
                    }
                }
            }
        }

        return urldecode($slug);
    }

    /**
     * Prepare a collection/array of posts
     *
     * @param mixed $posts
     * @return $this
     */
    public function preparePosts($posts)
    {
        foreach($posts as $post) {
            $post->setData(
                'permalink',
                $this->completePostSlug($post->getData('permalink'), $post->getId(), $post->getTypeInstance())
            );
        }

        return $this;
    }

    /**
     * Get the category IDs that are related to the postIds
     *
     * @param array $postIds
     * @param bool $getAllIds = true
     * @return array|false
     */
    public function getParentTermsByPostId($postId, $taxonomy = 'category')
    {
        $select = $this->getConnection()->select()
            ->distinct()
            ->from(array('_relationship' => $this->getTable('wordpress_term_relationship')), 'object_id')
            ->where('object_id = (?)', $postId)
            ->order('_term.term_id ASC');

        $select->join(
            array('_taxonomy' => $this->getTable('wordpress_term_taxonomy')),
            $this->getConnection()->quoteInto("_taxonomy.term_taxonomy_id = _relationship.term_taxonomy_id AND _taxonomy.taxonomy= ?", $taxonomy),
            '*'
        );

        $select->join(
            array('_term' => $this->getTable('wordpress_term')), 
            "`_term`.`term_id` = `_taxonomy`.`term_id`", 
            'name'
        );

        $this->addPrimaryCategoryToSelect($select, $postId);

        $select->reset('columns')
            ->columns(array(
                $taxonomy . '_id' => '_term.term_id', 
                'term_id' => '_term.term_id',
                'object_id'
            ))->limit(1);

        return $this->getConnection()->fetchAll($select);
    }

    public function addPrimaryCategoryToSelect($select, $postId)
    {
        return $this;
    }

    /**
     * Get the permalink SQL as a SQL string
     *
     * @return string
     */
    public function getPermalinkSqlColumn()
    {    
        $postTypes  = $this->postTypeManager->getPostTypes();
        $sqlColumns = array();
        $fields     = $this->getPermalinkSqlFields();

        foreach($postTypes as $postType) {    
            $tokens = $postType->getExplodedPermalinkStructure();
            $sqlFields = array();

            foreach($tokens as $token) {
                if (substr($token, 0, 1) === '%' && isset($fields[trim($token, '%')])) {
                    $sqlFields[] = $fields[trim($token, '%')];
                }
                else {
                    $sqlFields[] = "'" . $token . "'";
                }
            }    

            if (count($sqlFields) > 0) {
                $sqlColumns[$postType->getPostType()] = ' WHEN `post_type` = \'' . $postType->getPostType() . '\' THEN (CONCAT(' . implode(', ', $sqlFields) . '))';
            }
        }

        return count($sqlColumns) > 0 
            ? new \Zend_Db_Expr('(' . sprintf('CASE %s END', implode('', $sqlColumns)) . ')')
            : false;
    }

    /**
     * Get permalinks by the URI
     * Given a $uri, this will retrieve all permalinks that *could* match
     *
     * @param string $uri = ''
     * @param array $postTypes = null
     * @return false|array
     */
    public function getPermalinksByUri($uri = '')
    {
        $originalUri = $uri;
        $permalinks  = [];

        if ($postTypes = $this->postTypeManager->getPostTypes()) {
            $fields = $this->getPermalinkSqlFields();

            foreach($postTypes as $postType) {
                if (!($tokens = $postType->getExplodedPermalinkStructure())) {
                    continue;
                }

                $uri = $originalUri;

                if ($postType->permalinkHasTrainingSlash()) {
                    $uri = rtrim($uri, '/') . '/';
                }

                $filters = array();
                $lastToken = $tokens[count($tokens)-1];

                # Allow for trailing static strings (eg. .html)
                if (substr($lastToken, 0, 1) !== '%') {
                    if (substr($uri, -strlen($lastToken)) !== $lastToken) {
                        continue;
                    }

                    $uri = substr($uri, 0, -strlen($lastToken));

                    array_pop($tokens);
                }

                try {
                    for($i = 0; $i <= 1; $i++) {
                        if ($i === 1) {
                            $uri = implode('/', array_reverse(explode('/', $uri)));
                            $tokens = array_reverse($tokens);
                        }

                        foreach($tokens as $key => $token) {
                            if (substr($token, 0, 1) === '%') {
                                if (!isset($fields[trim($token, '%')])) {
                                    if ($taxonomy = $this->taxonomyManager->getTaxonomy(trim($token, '%'))) {
                                        $endsWithPostname = isset($tokens[$key+1]) && $tokens[$key+1] === '/' 
                                            && isset($tokens[$key+2]) && $tokens[$key+2] === '%postname%' 
                                            && !isset($tokens[$key+3]);

                                        if ($endsWithPostname) {
                                            $uri = rtrim(substr($uri, strrpos(rtrim($uri, '/'), '/')), '/');
                                            continue;
                                        }
                                    }

                                    break;
                                }

                                if (isset($tokens[$key+1]) && substr($tokens[$key+1], 0, 1) !== '%') {
                                    $filters[trim($token, '%')] = substr($uri, 0, strpos($uri, $tokens[$key+1]));
                                    $uri = substr($uri, strpos($uri, $tokens[$key+1]));
                                }
                                else if (!isset($tokens[$key+1])) {
                                    $filters[trim($token, '%')] = $uri;
                                    $uri = '';
                                }
                                else {
                                    throw new \Exception('Ignore me #1');
                                }
                            }
                            else if (substr($uri, 0, strlen($token)) === $token) {
                                $uri = substr($uri, strlen($token));
                            }
                            else {
                                throw new \Exception('Ignore me #2');
                            }

                            unset($tokens[$key]);
                        }
                    }

                    if ($buffer = $this->getPermalinks($filters, $postType)) {
                        foreach($buffer as $routeId => $route) {
                            if (rtrim($route, '/') === $originalUri) {
                                $permalinks[$routeId] = $route;
                                throw new \Exception('Break');
                            }    
                        }

#                        $permalinks += $buffer;
                    }
                }
                catch (\Exception $e) {
                    if ($e->getMessage() === 'Break') {
                        break;
                    }

                    // Exception thrown to escape nested loops
                }
            }
        }

        return count($permalinks) > 0 ? $permalinks : false;
    }

    /**
     * Get an array of post ID's and permalinks
     * $filters is applied but if empty, all permalinks are returned
     *
     * @param array $filters = array()
     * @return array|false
     */
    public function getPermalinks(array $filters = array(), $postType)
    {
        $tokens = $postType->getExplodedPermalinkStructure();
        $fields = $this->getPermalinkSqlFields();

        $select = $this->getConnection()
            ->select()
            ->from(array('main_table' => $this->getMainTable()), array('id' => 'ID', 'permalink' => $this->getPermalinkSqlColumn()))
            ->where('post_type = ?', $postType->getPostType())
            ->where('post_status IN (?)', array('publish', 'protected', 'private'));

        foreach($filters as $field => $value) {
            if (isset($fields[$field])) {
                $select->where($fields[$field] . ' = ?', urlencode($value));
            }
        }

        if ($routes = $this->getConnection()->fetchPairs($select)) {
            foreach($routes as $id => $permalink) {
                $routes[$id] = urldecode($this->completePostSlug($permalink, $id, $postType));
            }

            return $routes;
        }

        return false;
    }

    /**
     * Get the SQL data for the permalink
     *
     * @return array
     */
    public function getPermalinkSqlFields()
    {
        return array(
            'year' => 'SUBSTRING(post_date_gmt, 1, 4)',
            'monthnum' => 'SUBSTRING(post_date_gmt, 6, 2)',
            'day' => 'SUBSTRING(post_date_gmt, 9, 2)',
            'hour' => 'SUBSTRING(post_date_gmt, 12, 2)',
            'minute' => 'SUBSTRING(post_date_gmt, 15, 2)',
            'second' => 'SUBSTRING(post_date_gmt, 18, 2)',
            'post_id' => 'ID', 
            'postname' => 'post_name',
            'author' => 'post_author',
        );
    }

    /**
     * Determine whether the given post has any children posts
     *
     * @param \FishPig\WordPress\Model\Post $post
     * @return bool
     */
    public function hasChildrenPosts(\FishPig\WordPress\Model\Post $post)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), 'ID')
            ->where('post_parent=?', $post->getId())
            ->where('post_type=?', $post->getPostType())
            ->where('post_status=?', 'publish')
            ->limit(1);

        return $this->getConnection()->fetchOne($select) !== false;
    }

/**
     * Retrieve a collection of post comments
     *
     * @param \FishPig\WordPress\Model\Post $post
     * @return \FishPig\WordPress\Model\ResourceModel\Post\Comment\Collection
     */
    public function getPostComments(\FishPig\Wordpress\Model\Post $post)
    {
        return $this->factory->create('FishPig\WordPress\Model\ResourceModel\Post\Comment\Collection')
            ->setPost($post)
            ->addCommentApprovedFilter()
            ->addParentCommentFilter(0)
            ->addOrderByDate();
    }

    /**
     * Retrieve the featured image for the post
     *
     * @param \FishPig\WordPress\Model\Post $post
     * @return \FishPig\WordPress\Model\Image $image
     */
    public function getFeaturedImage(\FishPig\WordPress\Model\Post $post)
    {
        if ($images = $post->getImages()) {
            $select = $this->getConnection()
                ->select()
                ->from($this->getTable('wordpress_post_meta'), 'meta_value')
                ->where('post_id=?', $post->getId())
                ->where('meta_key=?', '_thumbnail_id')
                ->limit(1);

            if (($imageId = $this->getConnection()->fetchOne($select)) !== false) {
                return $this->factory->create('Image')->load($imageId);
            }
        }

        return false;
    }

    public function getPostsOnDayByYearMonth($dateStr)
    {
        $collection = $this->factory->create('FishPig\WordPress\Model\ResourceModel\Post\Collection')
            ->addPostDateFilter($dateStr)
            ->addIsViewableFilter();

        $collection->getSelect()
            ->distinct()
            ->setPart('columns', array())
            ->columns(array('posts_on_day' => 'SUBSTR(main_table.post_date, 9, 2)'));

        return $this->getConnection()->fetchCol($collection->getSelect());
    }
}
