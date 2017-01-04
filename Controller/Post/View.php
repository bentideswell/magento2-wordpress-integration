<?php
/**
 * @
**/
namespace FishPig\WordPress\Controller\Post;

class View extends \FishPig\WordPress\Controller\Action
{
	/**
	 * Load and return a Post model
	 *
	 * @return \FishPig\WordPress\Model\Post|false 
	**/
    protected function _getEntity()
    {
	    $post = $this->getFactory('Post')->create()->load(
	    	$this->getRequest()->getParam('id')
	    );

		return $post->getId() ? $post : false;
    }

	/**
	 * @return
	 **/
	protected function _getForward()
	{
		if ($this->_getEntity() && (int)$this->_getEntity()->getId() === (int)$this->getApp()->getBlogPageId()) {
			return $this->resultFactory
				->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
				->setModule('wordpress')
				->setController('homepage')
				->setParams(array('no_forward' => 1))
				->forward('view');
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
		
		/**
		 * Handle post type breadcrumb
		**/
		$postType = $this->getEntityObject()->getTypeInstance();
		
		if (!$postType->isDefault() && $postType->hasArchive()) {
			$crumbs['post_type'] = [
				'label' => __($postType->getName()),
				'title' => __($postType->getName()),
				'link' => $postType->getUrl(),
			];
		}
		
		return $crumbs;
    }
    
    /**
	 * @return array
	**/
    public function getLayoutHandles()
    {
	    $post = $this->_getEntity();
	    $postType = $post->getPostType();
	    
		if ($post->getPostType() == 'revision' && $post->getParentPost()) {
			$postType = $post->getParentPost()->getPostType();
		}
	    
	    return array_merge(
		    parent::getLayoutHandles(),
		    array(
				'wordpress_' . $postType . '_view',
				'wordpress_' . $postType . '_view_' . $post->getId(),
		    )
	    );
    }
}
