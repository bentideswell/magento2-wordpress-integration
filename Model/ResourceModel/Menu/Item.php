<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel\Menu;

use \FishPig\WordPress\Model\ResourceModel\Post;

class Item extends Post
{
    public function _construct()
    {
        $this->_init('wordpress_menu_item', 'ID');
    }
}
