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
     * @param  \FishPig\WordPress\Model\UrlInterface $url
     */
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url
    ) {
        $this->url = $url;
    }
    
    /**
     * @param  CspWhitelistXmlCollector $cspWhitelistXmlCollector
     * @param  $defaultPolicies = []
     * @return array
     */
    public function afterCollect(CspWhitelistXmlCollector $cspWhitelistXmlCollector, $defaultPolicies = []): array
    {
        $wpDomain = $this->getWPDomain();

        foreach ($this->getPolicyIds() as $policyId) {
            $defaultPolicies['fishpig_wp_' . $policyId] = new FetchPolicy(
                $policyId,
                false,
                [$wpDomain],
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
        
        return $defaultPolicies;
    }
    
    /**
     * @return string
     */
    private function getWPDomain(): string
    {
        $wpDomain = rtrim(str_replace(['https://', 'http://'], '', $this->url->getSiteUrl()), '/');
        
        if (($pos = strpos($wpDomain, '/')) !== false) {
            $wpDomain = substr($wpDomain, 0, $pos);
        }
        
        return $wpDomain;
    }
    
    /**
     * @return array
     */
    private function getPolicyIds(): array
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
}
