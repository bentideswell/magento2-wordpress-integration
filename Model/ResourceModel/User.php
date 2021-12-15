<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

class User extends AbstractResourceModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('users', 'ID');
    }
    
    /**
     * @return string
     */
    public function getMainTable()
    {
        if (empty($this->_mainTable)) {
            return parent::getMainTable();
        }

        return $this->getResourceConnection()->getTable($this->_mainTable, false);
    }
}
