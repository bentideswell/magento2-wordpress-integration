<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Sitemap;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;

class ItemProvider implements ItemProviderInterface
{
    /**
     *
     */
    public function __construct(
        \Magento\Store\Model\App\Emulation $emulation,
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\Model\NetworkInterface $network,
//        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $sitemap
        array $itemProviders = []
    ) {
        $this->emulation = $emulation;
        $this->appMode = $appMode;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
        $this->network = $network;
        $this->itemProviders = $itemProviders;
    }

    /**
     * @param int $storeId
     */
    final public function getItems($storeId)
    {
        $items = [];

        if (count($this->itemProviders) === 0) {
            return $items;
        }

        try {
            if (!$this->appMode->isDisabled()) {
                foreach ($this->itemProviders as $itemProvider) {
                    if (!($itemProvider instanceof ItemProviderInterface)) {
                        throw new \Exception(
                            get_class($itemProvider) . ' must implement ' . ItemProviderInterface::class
                        );
                    }

                    $items = array_merge(
                        $items,
                        $itemProvider->getItems($storeId)
                    );
                }
            }

            return $items;
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw $e;
        }
    }

    /**
     * @param  int $storeId
     * @return bool
     */
    protected function isEnabledForStore($storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            'wordpress/xmlsitemap/enabled', 
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
        );
    }
}
