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
	 * @return
	 */
	protected function _getForward()
	{
		
  	if ($entity = $this->_getEntity()) {
	  	$post = $this->registry->registry(Post::ENTITY);

  		if (!$post && ($homepageId = (int)$entity->getHomepagePageId())) {
  			return $this->resultFactory
  				->create(ResultFactory::TYPE_FORWARD)
  				->setModule('wordpress')
  				->setController('post')
  				->setParams(array('no_forward' => 1, 'id' => $homepageId))
  				->forward('view');
  		}
		}
		
		return parent::_getForward();
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
		
		if (!$this->_getEntity()->getBlogPageId()) {
			$handles[] = 'wordpress_front_page';
		}
		
		return array_merge($handles, parent::getLayoutHandles());
	}
}
