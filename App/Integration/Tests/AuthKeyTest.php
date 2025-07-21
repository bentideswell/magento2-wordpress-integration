<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Tests;

class AuthKeyTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * 
     */
    private $authKey = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\HTTP\AuthorisationKey $authKey
    ) {
        $this->authKey = $authKey;
    }

    /**
     *
     */
    public function runTest(): void
    {
        $this->authKey->getKey();
    }
}
