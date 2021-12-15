<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class IntegrationManager
{
    /**
     *
     */
    public function __construct(\FishPig\WordPress\App\Integration\Tests $integrationTests)
    {
        $this->integrationTests = $integrationTests;
    }

    /**
     * @return
     */
    public function runTests()
    {
        return $this->integrationTests->runTests();
    }
}
