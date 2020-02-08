<?php
/**
 *
*/
namespace FishPig\WordPress\Controller\User;

use FishPig\WordPress\Controller\Action;

class View extends Action
{  
    /**
     * @return
     */
    protected function _getEntity()
    {
        $object = $this->factory->create('User')->load(
            $this->getRequest()->getParam('author'),
            'user_nicename'
        );

        return $object->getId() ? $object : false;
    }

    /**
     * Get the blog breadcrumbs
     *
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        return array_merge( 
            parent::_getBreadcrumbs(), [
                'archives' => [
                    'label' => __($this->_getEntity()->getName()),
                    'title' => __($this->_getEntity()->getName())
                ]
            ]
        );
    }

    /**
     * @return array
     */
    public function getLayoutHandles()
    {
        return array_merge(parent::getLayoutHandles(), ['wordpress_user_view']);
    }
}
