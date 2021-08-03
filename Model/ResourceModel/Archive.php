<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
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

    /**
     * @return \Magento\Framework\DB\Select
     */
    public function getDatesForWidgetSelect()
    {
        return $this->getConnection()
            ->select()
                ->from(
                    ['main_table' => $this->getMainTable()],
                    [
                        'post_count' => new \Zend_Db_Expr('COUNT(ID)'),
                        'archive_date' => new \Zend_Db_Expr("CONCAT(SUBSTRING(post_date, 1, 4), '/', SUBSTRING(post_date, 6, 2))")
                    ]
                )->where(
                    'main_table.post_type=?', 'post'
                )->where(
                    'post_status = ?', 'publish'
                )->group(
                    'archive_date'
                )->order(
                    'archive_date DESC'
                );
    }
    
    /**
     * @return array
     */
    public function getDatesForWidget()
    {
        return $this->getConnection()->fetchAll($this->getDatesForWidgetSelect());
    }
}
