<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
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
     * @var array
     */
    protected $uriPermalinksMapCache = [];

    /**
     *
     *
     * @return
     */
    public function __construct(
        Context $context,
        WPContext $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Post\Permalink $permalinkResource,
        $connectionName = null
    ) {
        $this->permalinkResource = $permalinkResource;

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
     * @param string                   $field  - field to match $value to
     * @param string|int               $value  - $value to load record based on
     * @param Mage_Core_Model_Abstract $object - object we're trying to load to
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = $this->getConnection()->select()
            ->from(['e' => $this->getMainTable()])
            ->where("e.{$field}=?", $value)
            ->limit(1);

        $postType = $object->getPostType();

        if (!in_array($postType, ['*', ''])) {
            $select->where('e.post_type ' . (is_array($postType) ? 'IN' : '=') . ' (?)', $postType);
        }

        $select->columns(['permalink' => $this->permalinkResource->getPermalinkSqlColumn()]);

        return $this->filterLoadSelect($select, $object);
    }



    /**
     * Prepare a collection/array of posts
     *
     * @param  mixed $posts
     * @return $this
     */
    public function preparePosts($posts)
    {
        foreach ($posts as $post) {
            $post->setData(
                'permalink',
                $this->permalinkResource->completePostSlug(
                    $post->getData('permalink'), 
                    $post->getId(), 
                    $post->getTypeInstance()
                )
            );
        }

        return $this;
    }

    /**
     * Get the category IDs that are related to the postIds
     *
     * @param  array $postIds
     * @param  bool  $getAllIds = true
     * @return array|false
     */
    public function getParentTermsByPostId($postId, $taxonomy = 'category')
    {
        $select = $this->getConnection()->select()
            ->distinct()
            ->from(['_relationship' => $this->getTable('wordpress_term_relationship')], 'object_id')
            ->where('object_id = (?)', $postId)
            ->order('_term.term_id ASC');

        $select->join(
            ['_taxonomy' => $this->getTable('wordpress_term_taxonomy')],
            $this->getConnection()->quoteInto("_taxonomy.term_taxonomy_id = _relationship.term_taxonomy_id AND _taxonomy.taxonomy= ?", $taxonomy),
            '*'
        );

        $select->join(
            ['_term' => $this->getTable('wordpress_term')],
            "`_term`.`term_id` = `_taxonomy`.`term_id`",
            'name'
        );

        $this->addPrimaryCategoryToSelect($select, $postId);

        $select->reset('columns')
            ->columns(
                [
                $taxonomy . '_id' => '_term.term_id',
                'term_id' => '_term.term_id',
                'object_id'
                ]
            )->limit(1);

        return $this->getConnection()->fetchAll($select);
    }

    public function addPrimaryCategoryToSelect($select, $postId)
    {
        return $this;
    }




    /**
     * Determine whether the given post has any children posts
     *
     * @param  \FishPig\WordPress\Model\Post $post
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
     * @param  \FishPig\WordPress\Model\Post $post
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
     * @param  \FishPig\WordPress\Model\Post $post
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
            ->setPart('columns', [])
            ->columns(['posts_on_day' => 'SUBSTR(main_table.post_date, 9, 2)']);

        return $this->getConnection()->fetchCol($collection->getSelect());
    }
}
