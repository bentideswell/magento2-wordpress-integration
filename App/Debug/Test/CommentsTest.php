<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class CommentsTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\ResourceModel\Post\Comment\CollectionFactory $commentCollectionFactory
    ) {
        $this->commentCollectionFactory = $commentCollectionFactory;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        foreach ($this->commentCollectionFactory->create()->load() as $comment) {
            $comment->getId();
            $comment->getPost();
            $comment->getCommentDate('Y/m/d');
            $comment->getCommentTime('H:i:s');
            $comment->getCommentAuthorUrl();
            $comment->getGuid();
            $comment->getUrl();
            $comment->getCommentPageId();
            $comment->getChildrenComments();
            $comment->getAvatarUrl();
            $comment->isApproved();
            $comment->getAnchor();
            $comment->getPostTitle();
        }
    }
}
