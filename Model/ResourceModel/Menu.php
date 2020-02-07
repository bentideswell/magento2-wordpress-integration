<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

use \FishPig\WordPress\Model\ResourceModel\Term;

class Menu extends Term
{
    public function _construct()
    {
        $this->_init('wordpress_menu', 'term_id');
    }
}
