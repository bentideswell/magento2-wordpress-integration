<?php
/**
 *
**/
namespace FishPig\WordPress\Controller\Homepage;

class View extends \FishPig\WordPress\Controller\Action
{    
	/**
	 * @return
	 **/
	protected function _getEntity()
	{
		return $this->getFactory('Homepage')->create();
	}
	
	/**
	 * @return
	 **/
	protected function _getForward()
	{
		if ($this->getRequest()->getParam('preview') === 'true') {
		    if ($entity = $this->_getEntity()) {
			    $this->registry->unregister($entity::ENTITY);
			}

			return $this->resultFactory
				->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
				->setModule('wordpress')
				->setController('post')
				->setParams(array(
					'id' => $this->getRequest()->getParam('p'),
					'preview' => 'true',
				))
				->forward('view');
		}

		if ($homepageId = (int)$this->getApp()->getHomepagePageId()) {
			if ((int)$this->getRequest()->getParam('no_forward') === 0) {
				return $this->resultFactory
					->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
					->setModule('wordpress')
					->setController('post')
					->setParams(array('id' => $homepageId))
					->forward('view');
			}
		}
		
		return parent::_getForward();
	}
    
    /**
	  * Get the blog breadcrumbs
	  *
	  * @return array
	 **/
    protected function _getBreadcrumbs()
    {
	    $crumbs = parent::_getBreadcrumbs();
		
		if ($this->app->isRoot()) {
			$crumbs['blog'] = [
				'label' => __($this->_getEntity()->getName()),
				'title' => __($this->_getEntity()->getName())
			];
		}
		else {
			unset($crumbs['blog']['link']);
		}
		
		return $crumbs;
    }
}
