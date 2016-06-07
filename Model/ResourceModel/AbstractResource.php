<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model\ResourceModel;

abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	protected $_resource = null;
	protected $_factory = null;
	
    public function __construct(
    	\Magento\Framework\Model\ResourceModel\Db\Context $context,
    	\FishPig\WordPress\Model\App\ResourceConnection $resourceConnection,
    	\FishPig\WordPress\Model\App $app,
    	\FishPig\WordPress\Model\App\Factory $factory,
    	$connectionName = null
    )
    {
        parent::__construct($context, $connectionName);
        
        $this->_app = $app;
        $this->_resource = $resourceConnection;
        $this->_factory = $factory;
    }
    
	public function getApp()
	{
		return $this->_app;
	}
	
	public function getConnection()
	{
		return $this->_resource->getConnection();
		return $this->getApp()->getConnection();
	}

    
    public function getTable($tableName)
    {
		return $this->_resource->getTable($tableName);;
    }
    	
	/**
	 * Retrieve a meta value from the database
	 * This only works if the model is setup to work a meta table
	 * If not, null will be returned
	 *
	 * @param Fishpig_Wordpress_Model_Meta_Abstract $object
	 * @param string $metaKey
	 * @param string $selectField
	 * @return null|mixed
	 */
	public function getMetaValue(\Fishpig\Wordpress\Model\AbstractModel $object, $metaKey, $selectField = 'meta_value')
	{
		if ($object->hasMeta()) {
			$select = $this->getConnection()
				->select()
				->from($object->getMetaTable(), $selectField)
				->where($object->getMetaObjectField() . '=?', $object->getId())
				->where('meta_key=?', $metaKey)
				->limit(1);

			if (($value = $this->getConnection()->fetchOne($select)) !== false) {
				return trim($value);
			}
			
			return false;
		}
		
		return null;
	}

	/**
	 * Save a meta value to the database
	 * This only works if the model is setup to work a meta table
	 *
	 * @param Fishpig_Wordpress_Model_Meta_Abstract $object
	 * @param string $metaKey
	 * @param string $metaValue
	 */
	public function setMetaValue(\Fishpig\Wordpress\Model\AbstractModel $object, $metaKey, $metaValue)
	{
		if ($object->hasMeta()) {
			$metaValue = trim($metaValue);
			$metaData = array(
				$object->getMetaObjectField() => $object->getId(),
				'meta_key' => $metaKey,
				'meta_value' => $metaValue,
			);
							
			if (($metaId = $this->getMetaValue($object, $metaKey, $object->getMetaPrimaryKeyField())) !== false) {
				$this->_getWriteAdapter()->update($object->getMetaTable(), $metaData, $object->getMetaPrimaryKeyField() . '=' . $metaId);
			}
			else {
				$this->_getWriteAdapter()->insert($object->getMetaTable(), $metaData);
			}
		}
	}
	
	/**
	 * Get an array of all of the meta values associated with this post
	 *
	 * @param Fishpig_Wordpress_Model_Meta_Abstract $object
	 * @return false|array
	 */
	public function getAllMetaValues(\Fishpig\Wordpress\Model\AbstractModel $object)
	{
		if ($object->hasMeta()) {
			$select = $this->getConnection()
				->select()
				->from($object->getMetaTable(), array('meta_key', 'meta_value'))
				->where($object->getMetaObjectField() . '=?', $object->getId());

			if (($values = $this->getConnection()->fetchPairs($select)) !== false) {
				return $values;
			}
		}
		
		return false;
	}
}
