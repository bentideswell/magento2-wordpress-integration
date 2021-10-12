<?php
/**
 *
 */
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
