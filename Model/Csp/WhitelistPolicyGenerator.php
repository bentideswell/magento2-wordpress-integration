<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Csp;

class WhitelistPolicyGenerator implements \FishPig\WordPress\Api\Data\CspPolicyGeneratorInterface
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
     * @return array
     */
    public function getData(): array
    {
        $data = [
            $this->getDomain() => true
        ];

        return $data;
    }
    
    /**
     * @return string
     */
    private function getDomain(): string
    {
        $wpDomain = rtrim(str_replace(['https://', 'http://'], '', $this->url->getSiteUrl()), '/');
        
        if (($pos = strpos($wpDomain, '/')) !== false) {
            $wpDomain = substr($wpDomain, 0, $pos);
        }
        
        return $wpDomain;
    }
}
