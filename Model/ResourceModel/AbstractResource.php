<?php
/*
 * @category		Fishpig
 * @package		Fishpig_Wordpress
 * @license		http://fishpig.co.uk/license.txt
 * @author		Ben Tideswell <help@fishpig.co.uk>
 * @info			http://fishpig.co.uk/wordpress-integration.html
 */
namespace FishPig\WordPress\Model\ResourceModel;

/* Parent Class */
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/* Constructor Args */
use Magento\Framework\Model\ResourceModel\Db\Context;
use FishPig\WordPress\Model\Context as WPContext;

abstract class AbstractResource extends AbstractDb
{
	/*
	 *
	 */
	protected $wpContext;

	/*
	 *
	 */
	protected $resourceConnection = null;
	
	/*
	 *
	 *
	 * @return
	 */
	public function __construct(
	             Context $context,
						 WPContext $wpContext,
	                     $connectionName = null
  )
	{
		$this->wpContext          = $wpContext;
		$this->resourceConnection = $wpContext->getResourceConnection();

		parent::__construct($context, $connectionName);
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getConnection()
	{
		return $this->resourceConnection->getConnection();
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getTable($tableName)
	{
		return $this->resourceConnection->getTable($tableName);;
	}

	/*
	 *
	 *
	 * @return
	 */
	public function getTablePrefix()
	{
		return $this->resourceConnection->getTablePrefix();
	}
}
