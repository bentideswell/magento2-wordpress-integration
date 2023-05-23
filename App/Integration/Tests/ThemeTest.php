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
    private $theme = null;

    /**
     *
     */
    private $themeDeployer = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Theme\Deployer $themeDeployer
    ) {
        $this->theme = $theme;
        $this->themeDeployer = $themeDeployer;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        if (!$this->theme->isInstalled() || !$this->theme->isLatestVersion()) {
            $this->themeDeployer->deploy();
        }

        if (!$this->theme->isInstalled()) {
            throw new IntegrationFatalException(
                'The FishPig theme is not installed in WordPress. ' . $this->getErrorMessage()
            );
        }

        if (!$this->theme->isLatestVersion()) {
            throw new IntegrationFatalException(
                sprintf(
                    'The WordPress FishPig theme has an update available (latest=%s, current=%s). %s',
                    $this->theme->getLocalHash(),
                    $this->theme->getRemoteHash(),
                    $this->getErrorMessage()
                )
            );
        }
    }

    /**
     * @return string
     */
    private function getErrorMessage(): string
    {
        return sprintf(
            'Automatic WordPress theme installation/upgrade failed. You can manually generate the WordPress theme using the CLI command: "%s"',
            'bin/magento fishpig:wordpress:theme --zip'
        );
    }
}
