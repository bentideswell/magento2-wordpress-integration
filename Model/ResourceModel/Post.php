<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

class Post extends AbstractResourceModel
{
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
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \FishPig\WordPress\Model\ResourceModel\Context $wpContext,
        \FishPig\WordPress\Model\ResourceModel\Post\Permalink $permalinkResource,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \FishPig\WordPress\Model\ResourceModel\Post\Comment\CollectionFactory $commentCollectionFactory,
        $connectionName = null
    ) {
        $this->permalinkResource = $permalinkResource;
        $this->postCollectionFactory = $postCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;

        parent::__construct($context, $wpContext, $connectionName);
    }

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('posts', 'ID');
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
     * @param  int $postId
     * @param  string $taxonomy = 'category'
     * @return int
     */
    public function getParentTermId(int $postId, $taxonomy = 'category'): int
    {
        return $this->permalinkResource->getParentTermId($postId, $taxonomy);
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
            if ($permalink = $post->getData('permalink')) {
                $post->setData(
                    'permalink',
                    $this->permalinkResource->completePostSlug(
                        $permalink,
                        (int)$post->getId(), 
                        $post->getTypeInstance()
                    )
                );
            }
        }

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
        return $this->commentCollectionFactory->create()
            ->setPost(
                $post
            )->addCommentApprovedFilter(
                
            )->addParentCommentFilter(
                0
            )->addOrderByDate(
                
            );
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

    /**
     * ToDo: improve this
     */
    public function getPostsOnDayByYearMonth($dateStr)
    {
        $collection = $this->postCollectionFactory->create()
            ->addPostDateFilter($dateStr)
            ->addPostTypeFilter('post')
            ->addIsViewableFilter();

        $collection->getSelect()
            ->distinct()
            ->setPart('columns', [])
            ->columns(['posts_on_day' => 'SUBSTR(main_table.post_date, 9, 2)']);

        return $this->getConnection()->fetchCol($collection->getSelect());
    }
}
