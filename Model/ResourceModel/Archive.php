<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

class Archive extends AbstractResourceModel
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init('posts', 'ID');
    }

    /**
     * @return array
     */
    public function getDatesForWidget()
    {
        return $this->getConnection()->fetchAll(
            $this->getDatesForWidgetSelect()
        );
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
                        'archive_date' => new \Zend_Db_Expr(
                            "CONCAT(SUBSTRING(post_date, 1, 4), '/', SUBSTRING(post_date, 6, 2))"
                        )
                    ]
                )->where(
                    'main_table.post_type=?',
                    'post'
                )->where(
                    'post_status = ?',
                    'publish'
                )->group(
                    'archive_date'
                )->order(
                    'archive_date DESC'
                );
    }
}
