<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\ResourceConnection;

class OptionManager
{   
	/*
	 * @var array
	 */
	static protected $data = [];
	
	/*
	 * @var ResourceConnection
	 */
	protected $resourceConnection;

	/*
	 *
	 */
	public function __construct(ResourceConnection $resourceConnection)
	{
		$this->resourceConnection = $resourceConnection;
	}
	
	/*
	 * Get option value
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getOption($key)
	{
		if (!isset(self::$data[$key])) {
			$resource   = $this->resourceConnection;
			$connection = $resource->getConnection();
			
			$select = $connection->select()
				->from($resource->getTable('wordpress_option'), 'option_value')
				->where('option_name = ?', $key);

			self::$data[$key] = $connection->fetchOne($select);
		}

		return self::$data[$key];	
	}
	
	/*
	 * Get a site option.
	 * This is implemented in Multisite
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function getSiteOption($key)
	{
		return false;
	}
}
