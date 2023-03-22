<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP;

class Config
{
    /**
     * @auto
     */
    protected $scopeConfig = null;

    /**
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     *
     */
    public function getCurlOptionResolve(): ?array
    {
        if (null === ($value = $this->scopeConfig->getValue('wordpress/http/curlopt_resolve'))) {
            return null;
        }

        $value = array_filter(array_map(
            'trim',
            explode(',', $value)
        ));

        if (!$value) {
            return null;
        }

        return $value;
    }
}
