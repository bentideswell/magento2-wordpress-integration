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
     * @var \FishPig\WordPress\App\Logger $logger
     */
    private $logger = null;

    /**
     * @param \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface $storeSwitcherUrlProvider = null
     * @param \FishPig\WordPress\App\Logger $logger
     */
    public function __construct(
        \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface $storeSwitcherUrlProvider = null,
        \FishPig\WordPress\App\Logger $logger
    ) {        
        $this->storeSwitcherUrlProvider = $storeSwitcherUrlProvider;
        $this->logger = $logger;
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
            try{
                if ($redirectUrl = $this->storeSwitcherUrlProvider->getUrl($store)) {
                    return $redirectUrl;
                }
            } catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
        
        return $callback($store);
    }
}
