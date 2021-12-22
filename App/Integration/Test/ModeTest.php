<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

use FishPig\WordPress\Api\App\Integration\TestInterface;

class ModeTest implements TestInterface
{
    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Integration\Test\ModeTestResolver $modeTestResolver
    ) {
        $this->appMode = $appMode;
        $this->modeTestResolver = $modeTestResolver;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        $resolvedTestObject = $this->modeTestResolver->resolve();
        
        if (($resolvedTestObject instanceof TestInterface) === false) {
            throw new \FishPig\WordPress\App\Exception(
                get_class($resolvedTestObject) . ' must implement ' . TestInterface::class
            );
        }

        $resolvedTestObject->runTest();
    }
}
