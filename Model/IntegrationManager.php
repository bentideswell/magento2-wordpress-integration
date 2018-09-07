<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Integration\IntegrationException;
use FishPig\WordPress\Model\Logger;
use Exception;

class IntegrationManager
{
	/*
	 * @var Exception
	 */
	protected $state;

	/*
	 * @var IntegrationTests
	 */
	protected $integrationTests;
	
	/*
	 * @var Logger
	 */
	protected $logger;
	
	/*
	 *
	 *
	 */
	public function __construct(array $integrationTests, Logger $logger)
	{
		$this->integrationTests = $integrationTests;
		$this->logger           = $logger;
	}
	
	/*
	 *
	 * @return $this
	 */
	public function runTests()
	{
		if ($this->state === null) {
			try {
				foreach($this->integrationTests as $integrationTest) {
					$integrationTest->runTest();
				}
			}
			catch (Exception $e) {
				$this->state = $e;
				$this->logger->error($e);
			}
		}
		
		if ($this->state instanceof Exception) {
			throw $this->state;
		}

		return $this;
	}
}
