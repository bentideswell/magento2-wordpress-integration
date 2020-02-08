<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Model\Theme;
use FishPig\WordPress\Model\Integration\IntegrationException;

class ThemeTest
{
    /**
     * @var 
     */
    protected $theme;

    /**
     *
     */
    public function __construct(Theme $theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return 
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