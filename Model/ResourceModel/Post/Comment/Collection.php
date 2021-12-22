<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Post\Comment;

use FishPig\WordPress\Model\Post;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_post_comment_collection';
    protected $_eventObject = 'post_comments';

    /**
     * @var Post
     */
    private $post;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        string $modelName = null
    ) {
        $this->optionRepository = $optionRepository;

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
     * Order the comments by date
     *
     * @param  string $dir = null
     * @return $this
     */
    public function addOrderByDate($dir = null)
    {
        if ($dir === null) {
            $dir = $this->optionRepository->get('comment_order');
            $dir = in_array($dir, ['asc', 'desc']) ? $dir : 'asc';
        }

        $this->getSelect()->order('main_table.comment_date ' . $dir);

        return $this;
    }

    /**
     * Add parent comment filter
     *
     * @param  int $parentId = 0
     * @return $this
     */
    public function addParentCommentFilter($parentId = 0)
    {
        return $this->addFieldToFilter('comment_parent', $parentId);
    }

    /**
     *
     */
    public function setPost(Post $post)
    {
        $this->post = $post;
        return $this->addPostIdFilter($this->post->getId());
    }

    /**
     * Filters the collection of comments
     * so only comments for a certain post are returned
     *
     * @return $this
     */
    public function addPostIdFilter($postId)
    {
        return $this->addFieldToFilter('comment_post_ID', $postId);
    }

    /**
     * Filter the collection by a user's ID
     *
     * @param  int $userId
     * @return $this
     */
    public function addUserIdFilter($userId)
    {
        return $this->addFieldToFilter('user_id', $userId);
    }

    /**
     * Filter the collection by the comment_author_email column
     *
     * @param  string $email
     * @return $this
     */
    public function addCommentAuthorEmailFilter($email)
    {
        return $this->addFieldToFilter('comment_author_email', $email);
    }

    /**
     * Filters the collection so only approved comments are returned
     *
     * @return $this
     */
    public function addCommentApprovedFilter($status = 1)
    {
        return $this->addFieldToFilter('comment_approved', $status);
    }

    /**
     * @return $this
     */
    protected function _afterLoad()
    {
        if ($this->post) {
            foreach ($this->getItems() as $comment) {
                $comment->setPost($this->post);
            }
        }

        return parent::_afterLoad();
    }
}
