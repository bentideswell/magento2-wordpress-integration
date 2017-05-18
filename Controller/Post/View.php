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
		
		$crumbs['post'] = [
				'label' => __($this->_getEntity()->getName()),
				'title' => __($this->_getEntity()->getName())
		];

		return $crumbs;
    }
    
    /**
	 * @return array
	**/
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

		$layoutHandles = array(
			'wordpress_' . $postType . '_view',
			'wordpress_' . $postType . '_view_' . $post->getId(),
		);

		if (strpos($template, 'full-width') !== false) {
			$this->getPage()->getConfig()->setPageLayout('1column');

			$layoutHandles[] = 'wordpress_' . $postType . '_view_full_width';
			$layoutHandles[] = 'wordpress_' . $postType . '_view_full_width_' . $post->getId();
		}

		return array_merge(
			parent::getLayoutHandles(),
			$layoutHandles
        );
    }
}
