<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Tests;

class DatabaseTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @auto
     */
    protected $resourceConnection = null;

    /**
     * @param  \FishPig\WordPress\App\ResourceConnection $resourceConnection
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        $this->resourceConnection->isConnected();
    }
}
