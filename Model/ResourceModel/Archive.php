<?php
/**
 * @category    FishPig
 * @package     FishPig_WordPress
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

class Archive extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{
    /**
     * Set the table and primary key
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('wordpress_post', 'ID');
    }

    public function getDatesForWidget()
    {
        return $this->getConnection()
            ->fetchAll(
                "SELECT COUNT(ID) AS post_count, CONCAT(SUBSTRING(post_date, 1, 4), '/', SUBSTRING(post_date, 6, 2)) as archive_date 
                    FROM `" . $this->getMainTable() . "` AS `main_table` WHERE (`main_table`.`post_type`='post') AND (`main_table`.`post_status` ='publish') 
                    GROUP BY archive_date ORDER BY archive_date DESC"
            );
    }
}
