<?php
/**
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */

namespace FishPig\WordPress\Model\Meta;

abstract class AbstractMeta extends \FishPig\WordPress\Model\AbstractModel
{
	/**
	 * Array of entity's meta values
	 *
	 * @var array
	 */
	protected $_meta = array();
	
	abstract public function getMetaTableAlias();
	abstract public function getMetaTableObjectField();
	
	/**
	 * Determine whether a prefix is required
	 *
	 * @return bool
	**/
	public function doesMetaTableHavePrefix()
	{
		return false;
	}

	/**
	 * Retrieve the name of the meta database table
	 *
	 * @return false|string
	 */
	public function getMetaTable()
	{
		return $this->getResource()->getTable($this->getMetaTableAlias());
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
	 * Retrieve a meta value
	 *
	 * @param string $key
	 * @return false|string
	 */
	public function getMetaValue($key)
	{
		if (!isset($this->_meta[$key])) {
			$this->_meta[$key] = $value = $this->getResource()->getMetaValue($this, $this->_getRealMetaKey($key));
		}
		
		return $this->_meta[$key];
	}	
	
	/**
	 * Get an array of all of the meta values associated with this post
	 *
	 * @return false|array
	 */
	public function getAllMetaValues()
	{
		return $this->getResource()->getAllMetaValues($this);
	}
	
	/**
	 * Retrieve all of the meta data as an array
	 *
	 * @return false|array
	 */
	public function getMetaData()
	{
		return $this->_meta;
	}
	
	/**
	 * Changes the wp_ to the correct table prefix
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _getRealMetaKey($key)
	{
		if ($this->doesMetaTableHavePrefix()) {
			if (($tablePrefix = $this->getResource()->getTablePrefix()) !== 'wp_') {
				if (preg_match('/^(wp_)(.*)$/', $key, $matches)) {
					return $tablePrefix . $matches[2];
				}
			}
		}
		
		return $key;	
	}
}
