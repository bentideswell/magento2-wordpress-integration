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
}
