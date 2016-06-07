<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
 
namespace FishPig\WordPress\Model;

class Menu extends \FishPig\WordPress\Model\AbstractModel
{

	/**
	 * Event data
	 *
	 * @var string
	 */
	protected $_eventPrefix      = 'wordpress_menu';
	protected $_eventObject      = 'menu';
	
	public function _construct()
	{
		$this->_init('wordpress/menu');
	}
	
	/**
	 * Retrieve the taxonomy type
	 *
	 * @return string
	 */
	public function getTaxonomy()
	{
		return 'nav_menu';
	}
	
	/**
	 * Retrieve the root menu items
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Menu_Item_Collection
	 */
	public function getMenuItems()
	{
		return $this->_getObjectResourceModel()
			->addIsViewableFilter()
			->addTermIdFilter($this->getId(), $this->getTaxonomy());
	}

	/**
	 * Retrieve the object resource model
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Collection
	 */    
    protected function _getObjectResourceModel()
    {
	    return Mage::getResourceModel('wordpress/menu_item_collection')
	    	->addParentItemIdFilter(0);
    }
    
    /**
     * Inject links into $node
     *
     * @param Varien_Data_Tree_Node $node
     * @return bool
     */
    public function applyToTreeNode($node)
    {
		if (count($items = $this->getMenuItems()) > 0) {
			return $this->_injectLinks($items, $node);
		}
		
		return false;
    }
    
	/**
	 * Inject links into the top navigation
	 *
	 * @param Fishpig_Wordpress_Model_Resource_Menu_Item_Collection $items
	 * @param Varien_Data_Tree_Node $parentNode
	 * @return bool
	 */
	protected function _injectLinks($items, $parentNode)
	{
		if (!$parentNode) {
			return false;	
		}

		foreach($items as $item) {
			try {
				$nodeId = 'wp-node-' . $item->getId();
					
				$data = array(
					'name' => $item->getLabel(),
					'id' => $nodeId,
					'url' => $item->getUrl(),
					'is_active' => $item->isItemActive(),
				);
				
				if ($data['is_active']) {
					$parentNode->setIsActive(true);
					$buffer = $parentNode;
					
					while($buffer->getParent()) {
						$buffer = $buffer->getParent();
						$buffer->setIsActive(true);
					}
				}

				$itemNode = new Varien_Data_Tree_Node($data, 'id', $parentNode->getTree(), $parentNode);
				$parentNode->addChild($itemNode);
	
				if (count($children = $item->getChildrenItems()) > 0) {
					$this->_injectLinks($children, $itemNode);
				}
			}
			catch (Exception $e) {
				Mage::helper('wordpress')->log($e->getMessage());
			}
		}
		
		return true;
	}
}
