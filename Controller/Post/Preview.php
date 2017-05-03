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
	    if (0 === ($previewId = $this->_getPreviewId())) {
		    return false;
	    }
	    
	    $post = $this->getFactory('Post')->create()->load(
	    	$previewId
	    );

		return $post->getId() ? $post : false;
    }

	/**
	 * Get the correct preview ID
	 *
	 * @return int
	**/
	protected function _getPreviewId()
	{
		$keysToTry = array(
			'p',
			'preview_id',
			'id',
			'page_id'
		);
		
		foreach($keysToTry as $key) {
			if ($value = (int)$this->getRequest()->getParam($key)) {
				return $value;
			}
		}
		
		return false;
	}
	
	/**
	 * @return false
	 **/
	protected function _getForward()
	{
		return false;
	}
}
