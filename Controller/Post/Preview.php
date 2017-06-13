<?php
/**
 * @
**/
namespace FishPig\WordPress\Controller\Post;

use \FishPig\WordPress\Controller\Post\View as PostView;

class Preview extends PostView
{
	/**
	 * Load and return a Post model
	 *
	 * @return \FishPig\WordPress\Model\Post|false 
	**/
    protected function _getEntity()
    {
	    $post = $this->getFactory('Post')->create()->load(
	    	$this->getRequest()->getParam('preview_id')
	    );
	    
	    if ($revision = $post->getLatestRevision()) {
		    return $revision;
	    }

		return $post->getId() ? $post : false;
    }

	/**
	 * @return false
	 **/
	protected function _getForward()
	{
		return false;
	}
	
	/**
	 * @return false
	 **/
	protected function _canPreview()
	{
		return false;
	}
}
