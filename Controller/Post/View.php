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

    if (!$post->getId()) {
      return false;
    }

    return $post;
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
  		if ((int)$entity->getId() === (int)$this->getApp()->getBlogPageId()) {
  			return $this->resultFactory
  				->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
  				->setModule('wordpress')
  				->setController('homepage')
  				->setParams(array('no_forward' => 1))
  				->forward('view');
  		}
  		
  		if ($entity->getPostStatus() === 'private' && !$this->app->getConfig()->isLoggedIn()) {
    		return $this->_getNoRouteForward();
  		}
		}
		
		return parent::_getForward();
	}
	
  /*
	 *
	 */
  protected function _initLayout()
  {
	  parent::_initLayout();
	  
	  if ($commentId = (int)$this->getRequest()->getParam('comment-id')) {
		  $commentStatus = (int)$this->getRequest()->getParam('comment-status');
		  
		  if ($commentStatus === 0) {
				$this->messageManager->addSuccess(__('Your comment has been posted and is awaiting moderation.'));
		  }
		  else {
				$this->messageManager->addSuccess(__('Your comment has been posted.'));			  
		  }
	  }
		
		return $this;
  }

  /*
   * Get the blog breadcrumbs
   *
   * @return array
   */
  protected function _getBreadcrumbs()
  {
 		if ((int)$this->_getEntity()->getId() === (int)$this->getApp()->getHomepagePageId()) {
	 		return [];
	 	}
	 	
    $crumbs = parent::_getBreadcrumbs();
  
    // Handle post type breadcrumb
    $postType = $this->getEntityObject()->getTypeInstance();

    if ($crumbObjects = $postType->getBreadcrumbStructure($this->getEntityObject())) {
	    foreach($crumbObjects as $crumbType => $crumbObject) {
	      $crumbs[$crumbType] = [
	        'label' => __($crumbObject->getName()),
	        'title' => __($crumbObject->getName()),
	        'link' => $crumbObject->getUrl(),
	      ];
	    }
    }

    $crumbs['post'] = [
      'label' => __($this->_getEntity()->getName()),
      'title' => __($this->_getEntity()->getName())
    ];  

    return $crumbs;
  }
    
  /*
	 * @return array
	 */
  public function getLayoutHandles()
  {
    $post = $this->getEntityObject();
    $postType = $post->getPostType();
    
    if ($postType == 'revision' && $post->getParentPost()) {
      $postType = $post->getParentPost()->getPostType();
      $template = $post->getParentPost()->getMetaValue('_wp_page_template');
    }
    else {
      $template = $post->getMetaValue('_wp_page_template');
    }
    
    $layoutHandles = ['wordpress_post_view_default'];
    
		if ((int)$post->getId() === (int)$this->getApp()->getHomepagePageId()) {
			$layoutHandles[] = 'wordpress_front_page';
		}
		
    $layoutHandles[] = 'wordpress_' . $postType . '_view';
    $layoutHandles[] = 'wordpress_' . $postType . '_view_' . $post->getId();

    if ($template) {
    	$templateName = str_replace('.php', '', $template);
    	$layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName;
    	$layoutHandles[] = 'wordpress_' . $postType . '_view_' . $templateName . '_' . $post->getId();
    }

    return array_merge(parent::getLayoutHandles(), $layoutHandles);
  }
}
