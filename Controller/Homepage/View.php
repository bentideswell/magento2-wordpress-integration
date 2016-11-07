<?php
/**
 *
**/

namespace FishPig\WordPress\Controller\Homepage;

class View extends \FishPig\WordPress\Controller\Action
{    
	protected function _getEntity()
	{
		return $this->_factory->getFactory('Homepage')->create();
	}
	
    /**
	  * Get the blog breadcrumbs
	  *
	  * @return array
	 **/
    protected function _getBreadcrumbs()
    {
	    $crumbs = parent::_getBreadcrumbs();
			
		unset($crumbs['blog']['link']);
		
		return $crumbs;
    }
}
