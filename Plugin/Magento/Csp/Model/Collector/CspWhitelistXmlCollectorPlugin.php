<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Magento\Csp\Model\Collector;

use Magento\Csp\Model\Collector\CspWhitelistXmlCollector;
use FishPig\WordPress\Model\Csp\WhitelistPolicyCollector;

class CspWhitelistXmlCollectorPlugin
{
    /**
     * @auto
     */
    private $appMode = null;

    /**
     * @var WhitelistPolicyCollector
     */
    private $whitelistPolicyCollector = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        WhitelistPolicyCollector $whitelistPolicyCollector
    ) {
        $this->appMode = $appMode;
        $this->whitelistPolicyCollector = $whitelistPolicyCollector;
    }

    /**
     * @param  CspWhitelistXmlCollector $cspWhitelistXmlCollector
     * @param  $defaultPolicies = []
     * @return array
     */
    public function afterCollect(
        CspWhitelistXmlCollector $cspWhitelistXmlCollector,
        $defaultPolicies = []
    ): array {
        if ($this->appMode->isDisabled()) {
            return $defaultPolicies;
        }

        return $defaultPolicies + $this->whitelistPolicyCollector->collect();
    }
}
