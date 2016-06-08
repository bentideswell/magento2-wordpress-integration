<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block\Sidebar\Widget;

class Categories extends AbstractWidget
{
	/**
	 * Returns the current category collection
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Term_Collection
	 */
	public function getCategories()
	{
		$collection = $this->_factory->getFactory('Term')->create()->getCollection()
			->addTaxonomyFilter($this->getTaxonomy())
			->addParentIdFilter($this->getParentId())
			->addHasObjectsFilter();

		$collection->getSelect()
			->reset('order')
			->order('name ASC');

		return $collection;
	}
	
	public function getTaxonomy()
	{
		return $this->_getData('taxonomy') ? $this->_getData('taxonomy') : 'category';
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
	public function isCurrentCategory($category)
	{
		if ($this->getCurrentCategory()) {
			return $category->getId() == $this->getCurrentCategory()->getId();
		}
		
		return false;
	}
	
	/**
	 * Retrieve the current category
	 *
	 * @return Fishpig_Wordpress_Model_Category
	 */
	public function getCurrentCategory()
	{
		if (!$this->hasCurrentCategory()) {
			$this->setCurrentCategory($this->_registry->registry('wordpress_term'));
		}
		
		return $this->getData('current_category');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Categories');
	}
	
	/**
	 * Set the posts collection
	 *
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/categories.phtml');
		}

		return parent::_beforeToHtml();
	}
}
