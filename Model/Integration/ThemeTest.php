<?php
/*
 *
 */
namespace FishPig\WordPress\Model\Integration;

/* Constructor Args */
use FishPig\WordPress\Model\Theme;

/* Misc */
use FishPig\WordPress\Model\Integration\IntegrationException;

class ThemeTest
{
	/*
	 *
	 *
	 */
	protected $theme;

	/*
	 *
	 *
	 */
	public function __construct(Theme $theme)
	{
		$this->theme = $theme;
	}
	
	/*
	 *
	 *
	 */
	public function runTest()
	{
		if (!$this->theme->isThemeIntegrated()) {
			return $this;
		}
		
		$this->theme->validate();
		
		return $this;
	}
}