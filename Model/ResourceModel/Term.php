<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

class Term extends \FishPig\WordPress\Model\ResourceModel\AbstractResourceModel
{
    /**
     * @static bool
     */
    protected $useTermOrderField = null;

    public function _construct()
    {
        $this->_init('terms', 'term_id');
    }

    /**
     *
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = $this->getConnection()
            ->select()
                ->from(
                    [$this->tableAlias => $this->getMainTable()]
                )->where(
                    $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->tableAlias, $field)) . '=?',
                     $value
                );

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

        return $this->filterLoadSelect($select->limit(1), $object);
    }

    /**
     * Determine whether a 'term_order' field is present
     *
     * @return bool
     */
    public function tableHasTermOrderField(): bool
    {
        if ($this->useTermOrderField !== null) {
            return $this->useTermOrderField;
        }

        try {
            $this->useTermOrderField = $this->getConnection()->fetchOne(
                'SHOW COLUMNS FROM ' . $this->getMainTable() . ' WHERE Field = \'term_order\''
            ) !== false;
        } catch (Exception $e) {
            $this->useTermOrderField = false;
        }

        return $this->useTermOrderField;
    }

    /**
     * Get all child ID's for a parent
     * This includes recursive levels
     *
     * @param  int $parentId
     * @return array
     */
    public function getChildIds($parentId): array
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
