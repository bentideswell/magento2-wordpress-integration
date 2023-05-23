<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

interface DeploymentInterface
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
    public function isEnabled(): bool;

    /**
     *
     */
    public function deploy(): void;
}
