<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Integration\IntegrationException;
use Exception;

class IntegrationManager
{
	/*
	 *
	 * @var Exception
	 *
	 */
	protected $state;

	/*
	 *
	 *
	 */
	protected $integrationTests;
	
	/*
	 *
	 *
	 */
	public function __construct(array $integrationTests)
	{
		$this->integrationTests = $integrationTests;
	}
	
	/*
	 *
	 *
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
			}
		}
		
		if ($this->state instanceof Exception) {
			throw $this->state;
		}
		
		return $this;
	}
}
