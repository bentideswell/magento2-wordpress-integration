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
	}

    
    public function getTable($tableName)
    {
		return $this->_resource->getTable($tableName);;
    }
    
    public function getTablePrefix()
    {
	    return $this->_resource->getTablePrefix();
    }
    
    public function getFactory()
    {
	    return $this->_factory;
    }
}
