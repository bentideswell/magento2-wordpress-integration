<?php
/**
 * @deprecated 3.0.0
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class IntegrationManager
{
    /**
     * @auto
     */
    protected $integrationTests = null;

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
