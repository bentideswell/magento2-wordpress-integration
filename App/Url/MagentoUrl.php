<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Url;

class MagentoUrl implements \FishPig\WordPress\Api\App\Url\UrlInterface
{
    /**
     * @var []
     */
    private $cache = [];
    
    /**
     * @var array
     */
    private $currentUrl = [];

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
            $magentoUrl = $this->getBaseUrl();

            if ($this->isCustomBaseUrl()) {
                if (($pos = strpos($magentoUrl, '/', strlen('https://'))) !== false) {
                    $magentoUrl = substr($magentoUrl, 0, $pos);
                }
            }

            $this->cache[$storeId] = rtrim($magentoUrl, '/');
        }

        return $this->cache[$storeId];
    }

    /**
     * @return string
     */
    public function getCurrentUrl(): string
    {
        $store = $this->storeManager->getStore();
        $storeId = (int)$store->getId();

        if (!isset($this->currentUrl[$storeId])) {
            $this->currentUrl[$storeId] = preg_replace('/\?.*$/', '', $store->getCurrentUrl(false));

            if ($this->isCustomBaseUrl()) {
                $baseUrl = $this->getBaseUrl();
                $activeBaseUrl = $this->getUrl();

                if ($baseUrl !== $activeBaseUrl) {
                    $this->currentUrl[$storeId] = str_replace($baseUrl, $activeBaseUrl, $this->currentUrl[$storeId]);
                }
            }
        }
        
        return $this->currentUrl[$storeId];
    }
    
    /**
     * @return string
     */
    private function getBaseUrl(): string
    {
        return rtrim(
            str_ireplace(
                'index.php',
                '',
                $this->storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_LINK, 
                    $this->isFrontendUrlSecure()
                )
            ),
            '/'
        );
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

    /**
     * @return bool
     */
    private function isCustomBaseUrl(): bool
    {
        return (string)$this->scopeConfig->getValue(
            'wordpress/setup/custom_base_url'
        ) === \FishPig\WordPress\Model\Config\Source\MagentoBaseUrl::URL_USE_BASE;
    }
}
