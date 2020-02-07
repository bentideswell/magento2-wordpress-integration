<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Archive;

use FishPig\WordPress\Controller\Action;

class View extends Action
{    
    /**
     * Load the Archive model
     *
     * @return \FishPig\WordPress\Model\Archive
     */
    protected function _getEntity()
    {
        return $this->factory->create('Archive')->load(
            trim($this->_request->getParam('year') . '/' . $this->_request->getParam('month') . '/' . $this->_request->getParam('day'), '/')
        );
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
        ]]);
    }

    /**
     *
     * @return array
     */
    public function getLayoutHandles()
    {
    return array_merge(
        parent::getLayoutHandles(),
        ['wordpress_archive_view']
    );
    }
}
