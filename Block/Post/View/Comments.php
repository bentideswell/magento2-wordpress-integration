<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Block\Post\View;

class Comments extends \FishPig\WordPress\Block\Post\View\Comment\AbstractComment
{
    /**
     * Setup the pager and comments form blocks
     *
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('post/view/comments.phtml');
        }

        if ($this->getCommentCount() > 0 && ($pagerBlock = $this->getChildBlock('pager')) !== false) {
            $pagerBlock->setCollection($this->getComments());
        }

        if (($form = $this->getChildBlock('form')) !== false) {
            $form->setPost($this->getPost());
        }

        parent::_beforeToHtml();
    }

    /**
     * Get the HTML of the child comments
     *
     * @param \FishPig\WordPress\Model\Post\Comment $comment
     * @return string
     */
    public function getChildrenCommentsHtml(\FishPig\WordPress\Model\Post\Comment $comment)
    {
        return $this->getLayout()
            ->createBlock(get_class($this))
            ->setTemplate($this->getTemplate())
            ->setParentId($comment->getId())
            ->setComments($comment->getChildrenComments())
            ->toHtml();
    }
}
