<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Menu;

class Item extends \FishPig\WordPress\Model\ResourceModel\AbstractResourceModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('posts', 'ID');
    }
}
