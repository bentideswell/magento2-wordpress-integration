<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model;

use Magento\Framework\DataObject\IdentityInterface;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
	/**
	 * Name of entity meta table
	 * false if entity does not have a meta table
	 *
	 * @var string
	 */
	protected $_metaTable = false;
	
	/**
	 * Name of entity meta field
	 *
	 * @var false|string
	 */
	protected $_metaTableObjectField = false;

	/**
	 * Determine whether some meta fields have a prefix
	 * if true, the database table prefix is used
	 *
	 * @var bool
	 */
	protected $_metaHasPrefix = false;
	
	/**
	 * Array of entity's meta values
	 *
	 * @var array
	 */
	protected $_meta = array();

	protected $_app = null;
	protected $_wpUrlBuilder = null;
	protected $_factory = null;
	protected $_viewHelper = null;
	protected $_filter = null;
	
	public function __construct(
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\FishPig\WordPress\Model\App\Url $urlBuilder,
		\FishPig\WordPress\Model\App\Factory $factory,
		\FishPig\WordPress\Helper\View $viewHelper,
        \FishPig\WordPress\Helper\Filter $filter,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	) {
		parent::__construct($context, $registry, $resource, $resourceCollection);	
		
		$this->_wpUrlBuilder = $urlBuilder;
		$this->_factory = $factory;
		$this->_viewHelper = $viewHelper;
		$this->_filter = $filter;
	}
	
	
	
	public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

	public function getApp()
	{
		return $this->getResource()->getApp();
	}

	/**
	 * Retrieve the name of the meta database table
	 *
	 * @return false|string
	 */
	public function getMetaTable()
	{
		if ($this->hasMeta()) {
			return $this->getResource()->getTable($this->_metaTable);
		}
		
		return false;
	}
	
	/**
	 * Retrieve the name of the column used to identify the entity
	 *
	 * @return string
	 */
	public function getMetaObjectField()
	{
		return $this->_metaTableObjectField;
	}
	
	/**
	 * Retrieve the column name of the primary key fields
	 *
	 * @return string
	 */
	public function getMetaPrimaryKeyField()
	{
		return 'meta_id';
	}
	
	/**
	 * Determine whether the entity type has a meta table
	 *
	 * @return bool
	 */
	public function hasMeta()
	{
		return $this->_metaTable !== false && $this->_metaTableObjectField !== false;
	}
	
	/**
	 * Retrieve a meta value
	 *
	 * @param string $key
	 * @return false|string
	 */
	public function getMetaValue($key)
	{
		if ($this->hasMeta()) {
			if (!isset($this->_meta[$key])) {
				$this->_meta[$key] = $value = $this->getResource()->getMetaValue($this, $this->_getRealMetaKey($key));
				
				/*
				$meta = new \Magento\Framework\DataObject(array(
					'key' => $key,
					'value' => $value,
				));

				$this->_eventManager->dispatch($this->_eventPrefix . '_get_meta_value', array('object' => $this, $this->_eventObject => $this, 'meta' => $meta));
				
				$this->_meta[$key] = $meta->getValue();
				*/
			}
			
			return $this->_meta[$key];
		}
		
		return false;
	}
	
	/**
	 * Get an array of all of the meta values associated with this post
	 *
	 * @return false|array
	 */
	public function getAllMetaValues()
	{
		return $this->hasMeta()
			? $this->getResource()->getAllMetaValues($this)
			: false;
	}
	
	/**
	 * Retrieve all of the meta data as an array
	 *
	 * @return false|array
	 */
	public function getMetaData()
	{
		if ($this->hasMeta()) {
			return $this->_meta;
		}
		
		return false;
	}
	
	/**
	 * Changes the wp_ to the correct table prefix
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _getRealMetaKey($key)
	{
		if ($this->_metaHasPrefix) {
			$tablePrefix = $this->_app->getTablePrefix();

			if ($tablePrefix !== 'wp_') {
				if (preg_match('/^(wp_)(.*)$/', $key, $matches)) {
					return $tablePrefix . $matches[2];
				}
			}
		}
		
		return $key;	
	}
	
	/**
	 * Get a collection of posts
	 * Child class should filter posts accordingly
	 *
	 * @return Fishpig_Wordpress_Model_Resource_Post_Collection
	 */
	public function getPostCollection()
	{
		return $this->_factory->getFactory('Post')->create()->getCollection()->setFlag('source', $this);
	}
}
