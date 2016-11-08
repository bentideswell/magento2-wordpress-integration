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
		$crumbs = parent::_getBreadcrumbs();
		
		/**
		 * Handle post type breadcrumb
		**/
		$postType = $this->getEntityObject()->getTypeInstance();
		
		if (!$postType->isDefault() && $postType->hasArchive()) {
			$crumbs['post_type'] = [
				'label' => __($postType->getName()),
				'title' => __($postType->getName()),
				'link' => $postType->getUrl(),
			];
		}
		
		return $crumbs;
    }
}
