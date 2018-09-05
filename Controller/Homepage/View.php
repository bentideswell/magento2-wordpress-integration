<?php
/*
 *
 */
namespace FishPig\WordPress\Controller\Homepage;

/* Parent Class */
use FishPig\WordPress\Controller\Action;

/* Misc */
use FishPig\WordPress\Model\Homepage;
use FishPig\WordPress\Model\Post;
use Magento\Framework\Controller\ResultFactory;

class View extends Action
{    
	/*
   * @return Homepage
   */
	protected function _getEntity()
	{
		return $this->factory->get('Homepage');
	}

	/*
	 * @return bool
	 */
	protected function _canPreview()
	{
		return true;
	}
	
	/*
	 * Get the blog breadcrumbs
	 *
	 * @return array
	 */
	protected function _getBreadcrumbs()
	{
		$crumbs = parent::_getBreadcrumbs();
		
		if ($this->url->isRoot()) {
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
	
	/*
	 * Set the 'wordpress_front_page' handle if this is the front page
	 *
	 *
	 * @return array
	 */
	public function getLayoutHandles()
	{
		$handles = ['wordpress_homepage_view'];
		
		if (!$this->_getEntity()->getStaticFrontPageId()) {
			$handles[] = 'wordpress_front_page';
		}
		
		return array_merge($handles, parent::getLayoutHandles());
	}
}
