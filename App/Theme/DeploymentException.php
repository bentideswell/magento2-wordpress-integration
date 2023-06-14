<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class DeploymentException extends IntegrationFatalException
{
    /**
     *
     */
    const NO_DEPLOYMENTS = 324324;
}
