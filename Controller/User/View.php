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
	    $object = $this->_factory->getFactory('User')->create()->load(
	    	$this->_request->getParam('author'),
	    	'user_login'
	    );

		return $object->getId() ? $object : false;
    }
}
