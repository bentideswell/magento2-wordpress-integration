<?php
/*
 *
 */
namespace FishPig\WordPress\Controller\Term;
 
/* Parent Class */
use FishPig\WordPress\Controller\Action;

class View extends Action
{   
	/*
	 *
	 */
  protected function _getEntity()
  {
    $object = $this->factory->create('Term')->load((int)$this->getRequest()->getParam('id'));

		return $object->getId() ? $object : false;
  }
    
  /*
	 * Get the blog breadcrumbs
	 *
	 * @return array
	 */
  protected function _getBreadcrumbs()
  {
	  $crumbs = parent::_getBreadcrumbs();
	  $term   = $this->_getEntity();
	  
	  if ($taxonomy = $term->getTaxonomyInstance()) {
		  if ($taxonomy->isHierarchical()) {
				$buffer = $term;
				
				while($buffer->getParentTerm()) {
					$buffer = $buffer->getParentTerm();
					
					$crumbs['term_' . $buffer->getId()] = [
						'label' => __($buffer->getName()),
						'title' => __($buffer->getName()),
						'link' => $buffer->getUrl(),
					];
				}
			  
		  }
	  }

	  $crumbs['term'] = [
			'label' => __($term->getName()),
			'title' => __($term->getName())
		];
		
		return $crumbs;
  }
    
  /*
	 *
	 * @return array
	 *
	 */
  public function getLayoutHandles()
  {
	  if (!$this->_getEntity()) {
		  return [];
	  }

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
}
