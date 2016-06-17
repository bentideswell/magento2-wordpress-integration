<?php
/**
 *
**/

namespace FishPig\WordPress\Controller\Archive;

class View extends \FishPig\WordPress\Controller\Action
{    
	/**
	 * Load the Archive model
	 *
	 * @return \FishPig\WordPress\Model\Archive
	**/
	protected function _getEntity()
	{
		return $this->_factory->getFactory('Archive')->create()->load(
			trim($this->_request->getParam('year') . '/' . $this->_request->getParam('month') . '/' . $this->_request->getParam('day'), '/')
		);
	}
}
