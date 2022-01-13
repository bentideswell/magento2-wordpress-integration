<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class NetworkTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\NetworkInterface $network
    ) {
        $this->network = $network;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {    
        $this->network->isEnabled();
        $this->network->getBlogId();
        $this->network->getSiteId();
        $this->network->getNetworkObjects();
    }
}
