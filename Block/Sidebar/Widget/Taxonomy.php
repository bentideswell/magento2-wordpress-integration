<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Taxonomy extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * Returns the current category collection
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Term_Collection
	 */
	public function getTerms()
	{
		$collection = Mage::getResourceModel('wordpress/term_collection')
			->addTaxonomyFilter($this->getTaxonomy());

		$collection->getSelect()
			->reset('order')
			->order('name ASC');
			
			$collection->addParentIdFilter($this->getParentId())
				->addHasObjectsFilter();

		
		return $collection;
	}
	
	/**
	 * Returns the parent ID used to display categories
	 * If parent_id is not set, 0 will be returned and root categories displayed
	 *
	 * @return int
	 */
	public function getParentId()
	{
		return number_format($this->getData('parent_id'), 0, '', '');
	}
	
	/**
	 * Determine whether the category is the current category
	 *
	 * @param Fishpig_Wordpress_Model_Category $category
	 * @return bool
	 */
	public function isCurrentTerm($term)
	{
		if ($this->getCurrentTerm()) {
			if ((int)$term->getId() === (int)$this->getCurrentTerm()->getId()) {
				return true;
			}
			
			return in_array($term->getId(), $this->getActiveIds());
		}
		
		return false;
	}
	
	/**
	 * Get an array of active IDs
	 *
	 * @return array
	 */
	public function getActiveIds()
	{
		if (!$this->hasActiveIds() && ($current = $this->getCurrentTerm())) {
			$activeIds = array($current->getId());
			
			while($current->getParentTerm() !== false) {
				$current = $current->getParentTerm();
				$activeIds[] = $current->getId();
			}

			$this->setActiveIds($activeIds);
		}
		
		return $this->_getData('active_ids') ? $this->_getData('active_ids') : array();
	}
	
	/**
	 * Retrieve the current category
	 *
	 * @return Fishpig_Wordpress_Model_Category
	 */
	public function getCurrentTerm()
	{
		return Mage::registry('wordpress_term');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return null;
	}
	
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('wordpress/sidebar/widget/taxonomy.phtml');
		}

		return parent::_beforeToHtml();
	}
	
	/**
	 * Draw a child item
	 *
	 * @param Fishpig_Wordpress_Model_Term $term
	 * @param int $level = 0
	 * @return string
	 */
	public function drawChildItem(Fishpig_Wordpress_Model_Term $term, $level = 0)
	{
		$originalLevel = $this->getLevel();
		$this->setLevel($level);
		
		if ($this->getRendererTemplate()) {
			$this->setTemplate($this->getRendererTemplate());
		}
		else {
			$this->setTemplate('wordpress/sidebar/widget/taxonomy/renderer.phtml');
		}

		$html = $this->setTerm($term)->toHtml();	
		
		$this->setLevel($originalLevel);
		
		return $html;
	}
	
	/**
	 * Determines whether to show the post count
	 *
	 * @return bool
	 */
	public function canShowCount()
	{
		return false;
	}
	
	/**
	 * Determines whether the taxonomy is hierarchical
	 *
	 * @return bool
	 */
	public function isHierarchical()
	{
		return true;
	}
}
