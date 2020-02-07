<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Integration;

use FishPig\WordPress\Model\Network;
use FishPig\WordPress\Model\WPConfig;
use FishPig\WordPress\Model\Integration\IntegrationException;

class NetworkTest
{
    /**
     * @var Network
     */
    protected $network;

    /**
     * @var WPConfig
     */
    protected $wpConfig;

    /**
     *
     * @param Network $network
     * @param WPConfig $wpConfig
     */
    public function __construct(Network $network, WPConfig $wpConfig)
    {
        $this->network  = $network;
        $this->wpConfig = $wpConfig;
    }

    /**
     * This test checks for the situation where Multisite is enabled in WordPress
     * But the FishPig_WordPress_Multisite add-on is not installed in Magento
     *
     * @return $this
     */
    public function runTest()
    {
        if ((int)$this->wpConfig->getData('MULTISITE') === 0) {
            // Multisite not enabled in WordPress
            return $this;
        }

        if ($this->network->isEnabled()) {
            // Multisite module is installed in Magento
            return $this;
        }

        IntegrationException::throwException(sprintf(
            'The WordPress Network is active. You must install the FishPig_WordPress_Multisite add-on module. This can be found at %s',
            'https://fishpig.co.uk/magento/wordpress-integration/multisite/'
        ));
    }
}
