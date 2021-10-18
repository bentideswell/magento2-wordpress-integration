<?php
/**
 * @category FishPig
 * @package  FishPig_WordPress
 * @author   Ben Tideswell <help@fishpig.co.uk>
 */
namespace FishPig\WordPress\Model\ResourceModel;

class Term extends \FishPig\WordPress\Model\ResourceModel\AbstractResource
{

    /**
     * Determine whether there is a term order field
     *
     * @static bool
     */
    protected static $_tableHasTermOrder = null;

    public function _construct()
    {
        $this->_init('terms', 'term_id');
    }

    /**
     *
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);

        $select->reset('where');

        if (strpos($field, '.') !== false) {
            $select->where($field . '=?', $value);
        } else {
            $select->where("main_table.{$field}=?", $value);
        }

        $select->join(
            ['taxonomy' => $this->getTable('wordpress_term_taxonomy')],
            '`main_table`.`term_id` = `taxonomy`.`term_id`',
            ['term_taxonomy_id', 'taxonomy', 'description', 'count', 'parent']
        );

        if ($object->getTaxonomy()) {
            $select->where('taxonomy.taxonomy=?', $object->getTaxonomy());
        }

        return $select->limit(1);
    }

    /**
     * Determine whether a 'term_order' field is present
     *
     * @return bool
     */
    public function tableHasTermOrderField()
    {
        if (!is_null(self::$_tableHasTermOrder)) {
            return self::$_tableHasTermOrder;
        }

        try {
            self::$_tableHasTermOrder = $this->getConnection()
                ->fetchOne('SHOW COLUMNS FROM ' . $this->getMainTable() . ' WHERE Field = \'term_order\'')
                !== false;
        } catch (Exception $e) {
            self::$_tableHasTermOrder = false;
        }

        return self::$_tableHasTermOrder;
    }

    /**
     * Get all child ID's for a parent
     * This includes recursive levels
     *
     * @param  int $parentId
     * @return array
     */
    public function getChildIds($parentId)
    {
        $select = $this->getConnection()
            ->select()
            ->from($this->getTable('wordpress_term_taxonomy'), 'term_id')
            ->where('parent=?', $parentId)
            ->where('count>?', 0);

        if ($termIds = $this->getConnection()->fetchCol($select)) {
            foreach ($termIds as $key => $termId) {
                $termIds = array_merge($termIds, $this->getChildIds($termId));
            }

            return array_merge([$parentId], $termIds);
        }

        return [$parentId];
    }
}
