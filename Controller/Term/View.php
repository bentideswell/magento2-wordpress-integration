<?php
/**
 *
**/

namespace FishPig\WordPress\Controller\Term;
 
class View extends \FishPig\WordPress\Controller\Action
{   
	/**
	 * @var 
	**/
    protected function _getEntity()
    {
	    $object = $this->getFactory('Term')->create()->load($this->getRequest()->getParam('id'));

		return $object->getId() ? $object : false;
    }
    
    /**
	  * Get the blog breadcrumbs
	  *
	  * @return array
	 **/
    protected function _getBreadcrumbs()
    {
	    return array_merge(	
		    parent::_getBreadcrumbs(), [
			'archives' => [
				'label' => __($this->_getEntity()->getName()),
				'title' => __($this->_getEntity()->getName())
			]]
		);
    }
    
    /**
	 * @return array
	**/
    public function getLayoutHandles()
    {
	    $taxonomyType = $this->_getEntity()->getTaxonomyType();

	    return array_merge(
		    parent::getLayoutHandles(),
		    array(
			    'wordpress_term_view',
				'wordpress_' . $taxonomyType . '_view',
				'wordpress_' . $taxonomyType . '_view_' . $this->_getEntity()->getId(),
		    )
	    );
    }
    
    /*
	    		$this->_addCustomLayoutHandles(array(
			'wordpress_post_list',
			'wordpress_term_view',
			'wordpress_' . $term->getTaxonomyType() . '_view',
			'wordpress_' . $term->getTaxonomyType() . '_view_' . $term->getId(),
		));
		*/
}
