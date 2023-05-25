<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use FishPig\WordPress\App\Theme\DeploymentInterface;
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
    public function __construct(
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Logger $logger,
        Deployment\Pool $deploymentPool
    ) {
        $this->theme = $theme;
        $this->logger = $logger;
        $this->deploymentPool = $deploymentPool;
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
        $targetThemeHash = $this->theme->getLocalHash();
        $exception = null;

        foreach ($deployments as $deploymentId => $deployment) {
            if (!$deployment->isEnabled()) {
                continue;
            }

            try {
                $deployment->deploy();

                if ($this->theme->getRemoteHash() === $targetThemeHash) {
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
            'Deployment failed, but no exceptions were raised.'
        );
    }
}
