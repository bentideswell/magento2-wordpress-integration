<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Magento\Csp\Model\Collector;

use Magento\Csp\Model\Collector\CspWhitelistXmlCollector;
use Magento\Csp\Model\Policy\FetchPolicy;

class CspWhitelistXmlCollectorPlugin
{
    /**
     * @auto
     */
    protected $appMode = null;

    /**
     * @auto
     */
    protected $integrationTests = null;

    /**
     * @auto
     */
    protected $whitelistPolicyCollector = null;

    /**
     * @param  \FishPig\WordPress\Model\Csp\WhitelistPolicyCollector $whitelistPolicyCollector
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Integration\Tests $integrationTests,
        \FishPig\WordPress\Model\Csp\WhitelistPolicyCollector $whitelistPolicyCollector
    ) {
        $this->appMode = $appMode;
        $this->integrationTests = $integrationTests;
        $this->whitelistPolicyCollector = $whitelistPolicyCollector;
    }

    /**
     * @param  CspWhitelistXmlCollector $cspWhitelistXmlCollector
     * @param  $defaultPolicies = []
     * @return array
     */
    public function afterCollect(CspWhitelistXmlCollector $cspWhitelistXmlCollector, $defaultPolicies = []): array
    {
        if ($this->appMode->isDisabled()) {
            return $defaultPolicies;
        }

        try {
            if ($this->integrationTests->runTests() === false) {
                return $defaultPolicies;
            }

            if ($newPolicies = $this->whitelistPolicyCollector->collect()) {
                $defaultPolicies += $newPolicies;
            }
        } catch (\FishPig\WordPress\App\Integration\Exception\IntegrationFatalException  $e) {
            // Do nothing
        }

        return $defaultPolicies;
    }
}
