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
   * Get the blog breadcrumbs
   *
   * @return array
   */
  protected function _getBreadcrumbs()
  {
    $crumbs = parent::_getBreadcrumbs();
  
    // Handle post type breadcrumb
    $postType = $this->getEntityObject()->getTypeInstance();
  
    if (!$postType->isDefault() && $postType->hasArchive()) {
      $crumbs['post_type'] = [
        'label' => __($postType->getName()),
        'title' => __($postType->getName()),
        'link' => $postType->getUrl(),
      ];
    }
		
		if ($postType->isHierarchical()) {
			$parent = $this->_getEntity();
			
			while(($parent = $parent->getParentPost()) !== false) {
		    $crumbs['parent_post_' . $parent->getId()] = [
		      'label' => __($parent->getName()),
		      'title' => __($parent->getName()),
		      'link'  => $parent->getUrl()
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
