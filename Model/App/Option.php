<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use \FishPig\WordPress\Model\App\ResourceConnection;

class Option
{   
	/*
	 * @var array
	 */
	protected $data = [];
	
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
	 */
	public function getValue($key)
	{
		if (!isset($this->data[$key])) {
			$resource   = $this->resourceConnection;
			$connection = $resource->getConnection();
			
			$select = $connection>select()
				->from($resource->getTable('wordpress_option'), 'option_value')
				->where('option_name = ?', $key);

			$this->data[$key] = $connection->fetchOne($select);
		}

		return $this->data[$key];	
	}
}
