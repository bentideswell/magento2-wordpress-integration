<?php
/**
 *
 */
namespace FishPig\WordPress\Block\Post\View\Comment;

use FishPig\WordPress\Block\Post\View\Comment\AbstractComment;

class Wrapper extends AbstractComment
{
    /**
     * Setup the pager and comments form blocks
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('post/view/comment/wrapper.phtml');
        }

        if ($this->getCommentCount() > 0 && ($commentsBlock = $this->getChildBlock('comment_list')) !== false) {
            $commentsBlock->setComments($this->getComments());
        }    

        if ($this->getCommentCount() > 0 && ($pagerBlock = $this->getChildBlock('pager')) !== false) {
            $pagerBlock->setPost($this->getPost());
            $pagerBlock->setCollection($this->getComments());
        }

        if (($form = $this->getChildBlock('form')) !== false) {
            $form->setPost($this->getPost());
        }

        parent::_beforeToHtml();
    }

    /**
     * Get the comments HTML
     *
     * @return string
     */
    public function getCommentsHtml()
    {
        return $this->getChildHtml('comment_list');
    }
}
