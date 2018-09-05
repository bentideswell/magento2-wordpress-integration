<?php
/*
 *
 */
namespace FishPig\WordPress\Model\Integration;

/* Constructor Args */
use FishPig\WordPress\Helper\Core as CoreHelper;

/* Misc */
use FishPig\WordPress\Model\Integration\IntegrationException;

class CoreTest
{
	/*
	 *
	 *
	 */
	protected $coreHelper;

	/*
	 *
	 *
	 */
	public function __construct(CoreHelper $coreHelper)
	{
		$this->coreHelper = $coreHelper;
	}
	
	/*
	 *
	 *
	 */
	public function runTest()
	{
		return $this;
	}
}
