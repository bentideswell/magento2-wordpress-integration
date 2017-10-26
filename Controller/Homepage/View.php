<?php
/*
 *
 */
namespace FishPig\WordPress\Controller\Homepage;

class View extends \FishPig\WordPress\Controller\Action
{    
	/*
   * @return
   */
	protected function _getEntity()
	{
		return $this->getFactory('Homepage')->create();
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
	
	/*
	 * Set the 'wordpress_front_page' handle if this is the front page
	 *
	 *
	 * @return array
	 */
	public function getLayoutHandles()
	{
		$handles = ['wordpress_homepage_view'];
		
		if (!$this->getApp()->getBlogPageId()) {
			$handles[] = 'wordpress_front_page';
		}
		
		return array_merge($handles, parent::getLayoutHandles());
	}
}
