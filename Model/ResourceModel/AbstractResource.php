<?php
/*
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */
namespace FishPig\WordPress\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\Context;
use \FishPig\WordPress\Model\Context as WpContext;

abstract class AbstractResource extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/*
	 *
	 */
	protected $_resource = null;
	
	/*
	 *
	 */
	protected $_factory = null;

	protected $context;
	
	/*
	 *
	 *
	 * @return
	 */
	public function __construct(Context $context, WpContext $wpContext, $connectionName = null)
	{
		parent::__construct($context, $connectionName);

		$this->_app = $wpContext->getApp();
		$this->_resource = $wpContext->getResourceConnection();
		$this->_factory = $wpContext->getFactory();
		
		$this->context = $wpContext;
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getConnection()
	{
		return $this->_resource->getConnection();
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getTable($tableName)
	{
		return $this->_resource->getTable($tableName);;
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getTablePrefix()
	{
		return $this->_resource->getTablePrefix();
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getFactory()
	{
		return $this->_factory;
	}
}
