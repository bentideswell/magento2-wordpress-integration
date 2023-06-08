<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Csp;

use FishPig\WordPress\Api\Data\CspPolicyGeneratorInterface;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;
use Magento\Csp\Model\Policy\FetchPolicy;

class WhitelistPolicyCollector
{
    /**
     * @const string
     */
    const POLICY_ID_PREFIX = 'fishpig_wp_';

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
    protected $policyGeneratorPool = null;

    /**
     *
     */
    private $cache = null;

    /**
     *
     */
    private $storeManager = null;

    /**
     *
     */
    private $storeId = null;

    /**
     * @param  array $policyGeneratorPool = []
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Integration\Tests $integrationTests,
        \FishPig\WordPress\App\Cache $cache,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $policyGeneratorPool = []
    ) {
        $this->appMode = $appMode;
        $this->integrationTests = $integrationTests;
        $this->cache = $cache;
        $this->storeManager = $storeManager;
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

        if ($this->appMode->isDisabled()) {
            return $policies;
        }

        foreach ($this->policyGeneratorPool as $index => $policyGenerator) {
            if ($policiesData = $this->_collect($policyGenerator)) {
                foreach ($policiesData as $domain => $policyIds) {
                    if ($policyIds === true) {
                        $policyIds = $this->getDefaultPolicyIds();
                    } elseif ($policyIds === false) {
                        continue;
                    }

                    $domainId = $this->generateDomainId($domain);

                    foreach ($policyIds as $policyId) {
                        $uPolicyId = self::POLICY_ID_PREFIX . $domainId . '_' . $policyId;
                        $policies[$uPolicyId] = new FetchPolicy(
                            $policyId,
                            false,
                            [$domain]
                        );
                    }
                }
            }
        }

        return $policies;
    }

    /**
     * Allows the caching of policy data, which helps avoid integration tests
     */
    private function _collect(
        CspPolicyGeneratorInterface $policyGenerator
    ): array {
        $cacheKey = str_replace(
            '\\',
            '_',
            get_class($policyGenerator) . '_' . $this->getStoreId()
        );

        if ($result = $this->cache->load($cacheKey)) {
            return json_decode($result, true);
        }

        try {
            if ($this->integrationTests->runTests() === false) {
                return [];
            }
        } catch (IntegrationFatalException $e) {
            return [];
        }

        $result = $policyGenerator->getData();

        $this->cache->save(json_encode($result), $cacheKey);

        return $result;
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

    /**
     *
     */
    private function getStoreId(): int
    {
        if ($this->storeId === null) {
            $this->storeId = (int)$this->storeManager->getStore()->getId();
        }

        return $this->storeId;
    }
}
