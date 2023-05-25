<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme\Deployment;

use FishPig\WordPress\App\Theme\DeploymentInterface;

class Pool
{
    /**
     *
     */
    private $deployments = [];

    /**
     *
     */
    public function __construct(
        array $deployments = []
    ) {
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
    public function get(string $id): DeploymentInterface
    {
        if (!isset($this->deployments[$id])) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __(
                    'The deployment "%1" does not exist.',
                    $id
                )
            );
        }

        return $this->deployments[$id];
    }

    /**
     *
     */
    public function getAll(): array
    {
        return $this->deployments;
    }
}
