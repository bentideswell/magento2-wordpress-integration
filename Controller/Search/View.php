<?php
/**
 *
**/

namespace FishPig\WordPress\Controller\Search;
 
class View extends \FishPig\WordPress\Controller\Action
{
	public function _getEntity()
	{
		return $this->_factory->getFactory('Search')->create();
	}
}
