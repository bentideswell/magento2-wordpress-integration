<?php
/*
 *
 */
namespace FishPig\WordPress\Model\Integration;

/* Constructor Args */
use FishPig\WordPress\Model\DirectoryList;

/* Misc */
use FishPig\WordPress\Model\Integration\IntegrationException;

class PathTest
{
	/*
	 *
	 *
	 */
	protected $wpDirectoryList;

	/*
	 *
	 *
	 */
	public function __construct(DirectoryList $wpDirectoryList)
	{
		$this->wpDirectoryList = $wpDirectoryList;
	}
	
	/*
	 *
	 *
	 */
	public function runTest()
	{
		if ($this->wpDirectoryList->isValidBasePath() === false) {
			IntegrationException::throwException(
				'Unable to find a WordPress installation at specified path.'
			);
		}

		return $this;
	}
}
