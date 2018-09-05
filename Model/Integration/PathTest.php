<?php
/*
 *
 */
namespace FishPig\WordPress\Model\Integration;

/* Constructor Args */
use FishPig\WordPress\Model\Path;

/* Misc */
use FishPig\WordPress\Model\Integration\IntegrationException;

class PathTest
{
	/*
	 *
	 *
	 */
	protected $path;

	/*
	 *
	 *
	 */
	public function __construct(Path $path)
	{
		$this->path = $path;
	}
	
	/*
	 *
	 *
	 */
	public function runTest()
	{
		if ($this->path->getPath() === false) {
			IntegrationException::throwException(
				'Unable to find a WordPress installation at specified path.'
			);
		}

		return $this;
	}
}
