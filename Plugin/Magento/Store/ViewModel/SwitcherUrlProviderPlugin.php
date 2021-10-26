<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Magento\Store\ViewModel;

class SwitcherUrlProviderPlugin
{
    /**
     * @var \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface
     */
    private $storeSwitcherUrlProvider = null;

    /**
     * @param \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface $storeSwitcherUrlProvider = null
     */
    public function __construct(
        \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface $storeSwitcherUrlProvider = null
    ) {        
        $this->storeSwitcherUrlProvider = $storeSwitcherUrlProvider;
    }

    /**
     *
     */
    public function aroundGetTargetStoreRedirectUrl(
        \Magento\Store\ViewModel\SwitcherUrlProvider $subject, 
        \Closure $callback, 
        \Magento\Store\Model\Store $store
    ) {
        if ($this->storeSwitcherUrlProvider !== null) {
            if ($redirectUrl = $this->storeSwitcherUrlProvider->getUrl($store)) {
                return $redirectUrl;
            }
        }
        
        return $callback($store);
    }
}
