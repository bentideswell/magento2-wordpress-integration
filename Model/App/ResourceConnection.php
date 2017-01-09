<?php
/**
 * @var 
**/
namespace FishPig\WordPress\Model\App;

use \Magento\Framework\App\ResourceConnection\ConnectionFactory;
use \FishPig\WordPress\Model\Config;

class ResourceConnection
{
	/**
	 * @var 
	**/
	protected $_tablePrefix = '';
	
	/**
	 * @var 
	**/
	protected $_connectionFactory = null;
	
	/**
	 * @var 
	**/
	protected $_connection = null;
	
	/**
	 * @var 
	**/
	protected $_tables = array();
	
	/**
	 * @var 
	**/
	protected $_mappingData = array();
	
	/**
	 * @var 
	**/
	public function __construct(ConnectionFactory $connectionFactory)
	{
		$this->_connectionFactory = $connectionFactory;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function connect(array $config)
	{
		try {
			if ($this->_connection !== null) {
				throw new \Exception('A database connection already exists.');
			}
	
			$prefix = $this->_tablePrefix;
			
			$this->_applyMapping('before_connect');
			
			$this->_connection = $this->_connectionFactory->create($config);
			$this->_connection->query('SET NAMES UTF8');
			
			$this->_applyMapping('after_connect');
		}
		catch (\Exception $e) {
			\FishPig\WordPress\Model\App\Integration\Exception::throwException(
				'Error connecting to the WordPress database. Check the WordPress database details in wp-config.php.',
				$e->getMessage()
			);
		}
		
		return $this;
	}
	
	/**
	 *
	 *
	 * @param string
	 * @param int $blogId = 1
	 * @return $this
	**/
	protected function _applyMapping($type)
	{
		if (!empty($this->_mappingData[$type])) {
			$tables = $this->_mappingData[$type];

			foreach($tables as $alias => $table) {
				$this->_tables[$alias] = $this->_tablePrefix . $table;
			}
		}
		
		return $this;
	}
	
	/**
	 * Convert a table alias to a full table name
	 *
	 * @param string $alias
	 * @return string
	 **/
	public function getTable($alias)
	{
	    if (($key = array_search($alias, $this->_tables)) !== false) {
			if (strpos($key, 'wordpress_') === 0) {
				return $alias;
			}
	    }

		return isset($this->_tables[$alias])
			? $this->_tables[$alias]
			: $this->getTablePrefix() . $alias;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function setMappingData(array $data)
	{
		$this->_mappingData = $data;
		
		return $this;
	}

	/**
	 *
	 *
	 * @return 
	**/
	public function isConnected()
	{
		return $this->_connection !== null;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getConnection()
	{
		return $this->isConnected() ? $this->_connection : false;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function setTablePrefix($prefix)
	{
		$this->_tablePrefix = $prefix;
		
		return $this;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function getTablePrefix()
	{
		return $this->_tablePrefix;
	}
}
