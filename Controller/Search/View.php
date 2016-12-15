<?php
/**
 *
**/

namespace FishPig\WordPress\Controller\Search;
 
class View extends \FishPig\WordPress\Controller\Action
{
	public function _getEntity()
	{
		return $this->getFactory('Search')->create();
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
