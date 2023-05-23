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
    const DEPLOY_OK = true;
    const DEPLOY_FAIL = false;
    const DEPLOY_SKIPPED = null;

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
    private $deployments = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Logger $logger,
        array $deployments = []
    ) {
        $this->theme = $theme;
        $this->logger = $logger;

        foreach ($deployments as $id => $deployment) {
            if (false === ($deployment instanceof DeploymentInterface)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Deployment "%s" is not an instance of "%s".',
                        $id,
                        DeploymentInterface::class
                    )
                );
            }
        }

        $this->deployments = $deployments;
    }

    /**
     *
     */
    public function deploy(bool $force = false): ?bool
    {
        return $this->_deploy($this->deployments, $force);
    }

    /**
     *
     */
    public function deployUsing(string $deploymentId, bool $force = false): ?bool
    {
        if (!isset($this->deployments[$deploymentId])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Cannot find theme deployment service using "%s".',
                    $deploymentId
                )
            );
        }

        return $this->_deploy([$this->deployments[$deploymentId]], $force);
    }

    /**
     *
     */
    private function _deploy(array $deployments, bool $force = false): ?bool
    {
        if (!$force && $this->theme->isLatestVersion()) {
            return DeploymentInterface::DEPLOY_SKIPPED;
        }

        $targetThemeHash = $this->theme->getLocalHash();
        $exception = null;

        foreach ($deployments as $deploymentId => $deployment) {
            if (!$deployment->isEnabled()) {
                continue;
            }

            try {
                $deployment->deploy();

                if ($this->theme->getRemoteHash() === $targetThemeHash) {
                    return DeploymentInterface::DEPLOY_OK;
                }
            } catch (\Throwable $e) {
                $this->logger->error($e);

                $exception = new DeploymentException(
                    sprintf(
                        'Deployment "%s" exception: %s',
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

        return DeploymentInterface::DEPLOY_FAIL;
    }
}
