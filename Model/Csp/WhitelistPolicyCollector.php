<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Csp;

class WhitelistPolicyCollector
{
    /**
     * @const string
     */
    const POLCY_ID_PREFIX = 'fishpig_wp_';

    /**
     * @param  array $policyGeneratorPool = []
     */
    public function __construct(
        array $policyGeneratorPool = []
    ) {
        $this->policyGeneratorPool = $policyGeneratorPool;
    }
    
    /**
     * @param  CspWhitelistXmlCollector $cspWhitelistXmlCollector
     * @param  $defaultPolicies = []
     * @return array
     */
    public function collect(): array
    {
        $policies = [];
        
        foreach ($this->policyGeneratorPool as $policyGenerator) {
            if ($policiesData = $policyGenerator->getData()) {
                foreach ($policiesData as $domain => $policyIds) {
                    if ($policyIds === true) {
                        $policyIds = $this->getDefaultPolicyIds();
                    } elseif ($policyIds === false) {
                        continue;
                    }

                    $domainId = $this->generateDomainId($domain);
                    
                    foreach ($policyIds as $policyId) {
                        $uPolicyId = self::POLCY_ID_PREFIX . $domainId . '_' . $policyId;
                        $policies[$uPolicyId] = new \Magento\Csp\Model\Policy\FetchPolicy(
                            $policyId,
                            false,
                            [$domain],
                            [],
                            false,
                            false,
                            false,
                            [],
                            [],
                            false,
                            false
                        );
                    }
                }
            }
        }

        return $policies;
    }

    /**
     * @return array
     */
    private function getDefaultPolicyIds(): array
    {
        return [
            'default-src',
            'child-src',
            'connect-src',
            'font-src',
            'frame-src',
            'img-src',
//            'manifest-src',
            'media-src',
//            'object-src',
            'script-src',
            'style-src',
//            'base-uri',
            'form-action',
            'frame-ancestors'
        ];
    }

    /**
     * @param  string $domain
     * @return string
     */
    private function generateDomainId(string $domain): string
    {
        return str_replace('.', '_', $domain);
    }
}
