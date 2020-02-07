<?php
/**
 *
 */
namespace FishPig\WordPress\Controller\Search;

use FishPig\WordPress\Controller\Action;

/** Other */
use FishPig\WordPress\Model\Search;

class View extends Action
{  
    /**
     *
     *
     * @return Search
     */
    public function _getEntity()
    {
        return $this->factory->create('Search');
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
        ['wordpress_search_view']
    );
    }
}
