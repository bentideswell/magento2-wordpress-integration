<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use FishPig\WordPress\App\Theme\DeploymentInterface;
use FishPig\WordPress\App\Theme\DeploymentException;
use FishPig\WordPress\App\Exception;

class Deployer
{
    /**
     *
     */
    private $theme = null;

    /**
     *
     */
    private $logger = null;

    /**
     *
     */
    private $deploymentPool = null;

    /**
     *
     */
    private $appMode = null;

    /**
     *
     */
    private $themeUrl = null;

    /**
     *
     */
    private $option = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Logger $logger,
        Deployment\Pool $deploymentPool,
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Theme\Url $themeUrl,
        \FishPig\WordPress\App\Option $option
    ) {
        $this->theme = $theme;
        $this->logger = $logger;
        $this->deploymentPool = $deploymentPool;
        $this->appMode = $appMode;
        $this->themeUrl = $themeUrl;
        $this->option = $option;
    }

    /**
     *
     */
    public function isLatestVersion(): bool
    {
        return $this->theme->isInstalled() && $this->theme->isLatestVersion();
    }

    /**
     *
     */
    public function deploy(): ?string
    {
        return $this->_deploy($this->deploymentPool->getAll());
    }

    /**
     *
     */
    public function deployUsing(string $deploymentId, bool $force = false): ?string
    {
        $deployment = $this->deploymentPool->get($deploymentId);

        if (!$deployment->isEnabled()) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot deploy using "%s" with the current configuration.',
                    $deploymentId
                )
            );
        }

        return $this->_deploy([$deploymentId => $deployment], $force);
    }

    /**
     *
     */
    private function _deploy(array $deployments): ?string
    {
        try {
            $targetThemeHash = $this->theme->getLocalHash();
            $exception = null;

            foreach ($deployments as $deploymentId => $deployment) {
                if (!$deployment->isEnabled()) {
                    continue;
                }

                try {
                    $deployment->deploy();

                    if ($this->theme->getRemoteHash() === $targetThemeHash) {
                        $this->setThemeUpdateAvailableFlag(null);
                        return $deploymentId;
                    }
                } catch (\Throwable $e) {
                    $this->logger->error($e);

                    $exception = new DeploymentException(
                        sprintf(
                            'Exception during "%s" theme deployment: %s',
                            $deploymentId,
                            $e->getMessage()
                        ),
                        $e->getCode(),
                        $exception ?? null
                    );
                }
            }

            if ($exception) {
                throw $exception;
            }

            throw new DeploymentException(
                'No theme deployment service is available for your integration context.',
                DeploymentException::NO_DEPLOYMENTS
            );
        } catch (\Throwable $e) {
            $this->setThemeUpdateAvailableFlag(
                $this->theme->getLocalHash()
            );

            throw new DeploymentException(
                $this->getErrorMessage(),
                0,
                $e->getCode() === DeploymentException::NO_DEPLOYMENTS ? null : $e
            );
        }
    }

    /**
     * @return string
     */
    private function getErrorMessage(): string
    {
        $contextFailureStatus = 'failed';

        if ($this->appMode->isExternalMode()) {
            $contextFailureStatus = 'is not possible in external mode';
        }

        $debugVars = '';
        $currentVersion = $this->theme->isInstalled() ? $this->theme->getRemoteHash() : '** not installed **';
        $requiredVersion = $this->theme->getLocalHash();

        if ($currentVersion !== $requiredVersion) {
            $debugVars = sprintf(
                " Current Version: %s\nRequired Version: %s\n\n",
                $currentVersion,
                $requiredVersion
            );
        }

        return sprintf(
            "\n\nA WordPress theme update is available but automatic theme installation %s.\n\nManual installation is required!\n\nYou can access the latest version of theme by one of the following methods:\n\n     Browser: %s\n CLI command: %s\n\nLogin to the WP Admin and go to Appearance > Themes > Add New > Upload Theme and then upload the ZIP file.\n\n%s",
            $contextFailureStatus,
            $this->themeUrl->getUrl(),
            'bin/magento fishpig:wordpress:theme --zip',
            $debugVars
        );
    }

    /**
     *
     */
    private function setThemeUpdateAvailableFlag($value): void
    {
        $this->option->set(
            'fishpig-theme-update-available-hash',
            $value
        );

        $this->option->set(
            'fishpig-theme-update-available-url',
            $value !== null ? $this->themeUrl->getUrl() : null
        );
    }
}
