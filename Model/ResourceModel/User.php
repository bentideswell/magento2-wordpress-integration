<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

class User extends \FishPig\WordPress\Model\ResourceModel\Meta\AbstractMeta
{
    public function _construct()
    {
        $this->_init('wordpress_user', 'ID');
    }
}
