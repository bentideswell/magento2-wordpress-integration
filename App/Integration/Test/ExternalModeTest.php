<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

class ExternalModeTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\ResourceConnection $resourceConnection
    ) {
        $this->appMode = $appMode;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        if (!$this->appMode->isExternalMode()) {
            throw new \FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException(
                __('Invalid mode')
            );
        }

        $this->resourceConnection->isConnected();
    }
}
