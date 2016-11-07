<?php
/**
 * @
**/

namespace FishPig\WordPress\Controller\Post;

class View extends \FishPig\WordPress\Controller\Action
{
	/**
	 * Load and return a Post model
	 *
	 * @return \FishPig\WordPress\Model\Post|false 
	**/
    protected function _getEntity()
    {
	    $post = $this->_factory->getFactory('Post')->create()->load(
	    	$this->_request->getParam('id')
	    );

		return $post->getId() ? $post : false;
    }
    
    /**
	  * Get the blog breadcrumbs
	  *
	  * @return array
	 **/
    protected function _getBreadcrumbs()
    {
	    return array_merge(	
		    parent::_getBreadcrumbs(), [
			'archives' => [
				'label' => __($this->_getEntity()->getName()),
				'title' => __($this->_getEntity()->getName())
			]]
		);
    }
}
