<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel\Menu\Item;

use \FishPig\WordPress\Model\ResourceModel\Post\Collection as PostCollection;

class Collection extends PostCollection
{
    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = 'wordpress_menu_item_collection';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = 'menu_items';

    /**
     * Initialise the object
     *
     */
    public function _construct()
    {
        $this->_init('FishPig\WordPress\Model\Menu\Item', 'FishPig\WordPress\Model\ResourceModel\Menu\Item');

        $this->addPostTypeFilter('nav_menu_item');
    }

    /**
     * Ensures that only posts and not pages are returned
     * WP stores posts and pages in the same DB table
     *
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->order('menu_order ASC');

        return $this;
    }

    /**
     * Filter the collection by parent ID
     * Set 0 as $parentId to return root menu items
     *
     * @param int $parentId = 0
     * @return $this
     */
    public function addParentItemIdFilter($parentId = 0)
    {
        return $this->addMetaFieldToFilter('_menu_item_menu_item_parent', $parentId);
    }
}
