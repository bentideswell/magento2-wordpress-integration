<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Block_Sidebar_Widget_Links extends Fishpig_Wordpress_Block_Sidebar_Widget_Abstract
{
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return $this->__('Links');
	}
	
	/**
	 * Retrieve a collection of links
	 *
	 * @return Fishpig_Wordpress_Model_Mysql4_Link_Collection
	 */
	public function getLinks()
	{
		if (!$this->hasLinks()) {
			if ($this->getLinkCategory()) {
				$links = $this->getLinkCategory()->getLinks();
			}
			else {
				$links = Mage::getResourceModel('wordpress/link_collection');
			}			

			$this->setLinks($links);
		}
		
		return $this->_getData('links');
	
	}
	
	/**
	 * Retrieve the link category
	 *
	 * @return Fishpig_Wordpress_Model_Link_Category
	 */
	public function getLinkCategory()
	{
		if (!$this->hasLinkCategory() && $this->_getData('category')) {
			$this->setLinkCategory(false);

			$category = Mage::getModel('wordpress/link_category')->load($this->_getData('category'));
			
			if ($category->getId()) {
				$this->setLinkCategory($category);
			}
		}
		
		return $this->_getData('link_category');
	}
	
	/**
	 * Determine whether to display the link description
	 *
	 * @return bool
	 */
	public function displayLinkDescription()
	{
		return $this->_getData('description') == 1;
	}
}
