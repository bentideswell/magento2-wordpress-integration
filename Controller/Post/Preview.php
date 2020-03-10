<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Post;

use FishPig\WordPress\Controller\Post\View;

class Preview extends View
{
    /**
     * Load and return a Post model
     *
     * @return \FishPig\WordPress\Model\Post|false 
     */
    protected function _getEntity()
    {
        $post = $this->factory->create('Post')->load(
            (int)$this->getRequest()->getParam('preview_id')
        );

        if (!$post->getId()) {
            return false;
        }

        return $post;
        return ($revision = $post->getLatestRevision()) ? $revision : $post;
    }

    /**
     * @return false
     */
    protected function _getForward()
    {
        return false;
    }

    /**
     * @return false
     */
    protected function _canPreview()
    {
        return false;
    }
}
