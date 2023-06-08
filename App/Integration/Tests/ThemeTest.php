<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Tests;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;


class ThemeTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     *
     */
    private $themeDeployer = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\Deployer $themeDeployer
    ) {
        $this->themeDeployer = $themeDeployer;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        if (!$this->themeDeployer->isLatestVersion()) {
            $this->themeDeployer->deploy();
        }
    }
}
