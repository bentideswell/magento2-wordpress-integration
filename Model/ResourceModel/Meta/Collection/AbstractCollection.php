<?php
/**
 *
 */
namespace FishPig\WordPress\Model\ResourceModel\Meta\Collection;

use FishPig\WordPress\Model\ResourceModel\Collection\AbstractCollection as ParentClass;

abstract class AbstractCollection extends ParentClass
{
    /**
     * An array of all of the meta fields that have been joined to this collection
     *
     * @var array
     */
    protected $metaFieldsJoined = [];

    /**
     * Add a meta field to the select statement columns section
     *
     * @param string $field
     * @return $this
     */
    public function addMetaFieldToSelect($metaKey)
    {
        if (($field = $this->_joinMetaField($metaKey)) !== false) {
            $this->getSelect()->columns(array($metaKey => $field));
        }

        return $this;
    }

    /**
     * Add a meta field to the filter (where) part of the query
     *
     * @param string $field
     * @param string|array $filter
     * @return $this
     */
    public function addMetaFieldToFilter($metaKey, $filter)
    {
        if (($field = $this->_joinMetaField($metaKey)) !== false) {
            $this->addFieldToFilter($field, $filter);
        }

        return $this;
    }

    /**
     * Add a meta field to the SQL order section
     *
     * @param string $field
     * @param string $dir = 'asc'
     * @return $this
     */
    public function addMetaFieldToSort($field, $dir = 'asc')
    {
        $this->getSelect()->order($field . ' ' . $dir);

        return $this;
    }

    /**
     * Join a meta field to the query
     *
     * @param string $field
     * @return $this
     */
    protected function _joinMetaField($field)
    {
        $model = $this->getNewEmptyItem();

        if (!isset($this->metaFieldsJoined[$field])) {
            $alias = $this->_getMetaFieldAlias($field);

            $meta = new \Magento\Framework\DataObject(array(
                'key' => $field,
                'alias' => $alias,
            ));

            $this->_eventManager->dispatch($model->getEventPrefix() . '_join_meta_field', ['collection' => $this, 'meta' => $meta]);

            if ($meta->getCanSkipJoin()) {
                $this->metaFieldsJoined[$field] = $meta->getAlias();
            }
            else {
                $condition = "`{$alias}`.`{$model->getMetaTableObjectField()}`=`main_table`.`{$model->getResource()->getIdFieldName()}` AND "
                    . $this->getConnection()->quoteInto("`{$alias}`.`meta_key`=?", $field);

                $this->getSelect()->joinLeft(array($alias => $model->getMetaTable()), $condition, '');

                $this->metaFieldsJoined[$field] = $alias . '.meta_value';;
            }
        }

        return $this->metaFieldsJoined[$field];
    }

    /**
     * Convert a meta key to it's alias
     * This is used in all SQL queries
     *
     * @param string $field
     * @return string
     */
    protected function _getMetaFieldAlias($field)
    {
        return 'meta_field_' . str_replace('-', '_', $field);
    }
}
