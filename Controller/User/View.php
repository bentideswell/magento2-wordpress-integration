<?php
/**
 *
**/

namespace FishPig\WordPress\Controller\User;
 
class View extends \FishPig\WordPress\Controller\Action
{
	/**
	 * @var 
	**/
    protected function _getEntity()
    {
	    $object = $this->getFactory('User')->create()->load(
	    	$this->getRequest()->getParam('author'),
	    	'user_nicename'
	    );

		return $object->getId() ? $object : false;
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
