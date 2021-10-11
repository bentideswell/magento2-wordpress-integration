<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

class ThemeTest implements \FishPig\WordPress\Api\Data\App\Integration\TestInterface
{
    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\Model\Theme $theme
    ) {
        $this->appMode = $appMode;
        $this->theme = $theme;
    }
    
    /**
     * @return void
     */
    public function runTest(): void
    {
        // Do nothing
    }
}
