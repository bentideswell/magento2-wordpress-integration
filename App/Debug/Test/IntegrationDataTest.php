<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class IntegrationDataTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Api\IntegrationDataRetriever $integrationData
    ) {
        $this->integrationData = $integrationData;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $integrationData = $this->integrationData->getData();

        if (!$integrationData || !is_array($integrationData)) {
            throw new \Exception('Integration data is missing.');
        }
    }
}
