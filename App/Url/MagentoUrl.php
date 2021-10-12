<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Url;

class MagentoUrl implements \FishPig\WordPress\Api\Data\App\Url\UrlInterface
{
    /**
     * @var []
     */
    private $cache = [];

    /**
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }
    
    /**
     * @return string
     */
    public function getUrl(): string
    {
        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();

        if (!isset($this->cache[$storeId])) {
            $magentoUrl = rtrim(
                str_ireplace(
                    'index.php',
                    '',
                    $store->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_LINK, 
                        $this->isFrontendUrlSecure()
                    )
                ),
                '/'
            );

            if ($this->ignoreStoreCode()) {
                $storeCode = $store->getCode();

                if (substr($magentoUrl, -strlen($storeCode)) === $storeCode) {
                    $magentoUrl = substr($magentoUrl, 0, -strlen($storeCode)-1);
                }
            }

            $this->cache[$storeId] = rtrim($magentoUrl, '/');
        }

        return $this->cache[$storeId];
    }

    /**
     * @return bool
     */
    private function ignoreStoreCode(): bool
    {
        return false;
    }

    /**
     * @return bool
     */
    private function isFrontendUrlSecure(): bool
    {
        return $this->scopeConfig->isSetFlag(
            'web/secure/use_in_frontend',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            (int)$this->storeManager->getStore()->getId()
        );
    }
}
