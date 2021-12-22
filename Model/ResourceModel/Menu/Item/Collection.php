<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Menu\Item;

class Collection extends \FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_menu_item_collection';
    protected $_eventObject = 'menu_items';

    /**
     * @var int
     */
    private $menuId = null;

    /**
     * Ensures that only posts and not pages are returned
     * WP stores posts and pages in the same DB table
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()
            ->where('post_type = ?', 'nav_menu_item')
            ->where('post_status = ?', 'publish')
            ->order('menu_order ASC');
        
        return $this;
    }

    /**
     * @param  int $parentId = 0
     * @return self
     */
    public function addParentItemIdFilter(int $parentId = 0): self
    {
        $this->getSelect()->join(
            ['menu_parent_item_id' => $this->getTable('postmeta')],
            implode(
                ' AND ',
                [
                    'menu_parent_item_id.post_id = main_table.ID',
                    'menu_parent_item_id.meta_key = \'_menu_item_menu_item_parent\'',
                    'menu_parent_item_id.meta_value = ' . (int)$parentId
                ]
            ),
            null
        );

        return $this;
    }
    
    /**
     * @return self
     */
    public function addMenuFilter(\FishPig\WordPress\Model\Menu $menu): self
    {
        if ($this->menuId !== null) {
            return $this;
        }
        
        $this->menuId = $menu->getId();
        $type = $menu->getTaxonomy();

        $this->getSelect()->distinct()->join(
            ["rel_$type" => $this->getTable('term_relationships')],
            "rel_$type.object_id = main_table.ID",
            null
        )->join(
            ["tax_$type" => $this->getTable('term_taxonomy')],
            "tax_$type.term_taxonomy_id=rel_$type.term_taxonomy_id AND tax_$type.taxonomy='$type'",
            null
        )->join(
            ["terms_$type" => $this->getTable('terms')],
            "terms_$type.term_id = tax_$type.term_id",
            null
        )->where(
            "terms_$type.term_id = ?",
            $this->menuId
        );

        return $this;
    }
}
