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
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \FishPig\WordPress\Model\Config\Source\MagentoBaseUrl $magentoBaseUrlSource
    ) {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->magentoBaseUrlSource = $magentoBaseUrlSource;
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
    public function getCurrentUrl($withQuery = false): string
    {
        $store = $this->storeManager->getStore();
        $cacheKey = (int)$store->getId() . '-' . (int)$withQuery;

        if (!isset($this->currentUrl[$cacheKey])) {
            $storeUrl = $store->getCurrentUrl(false);

            if (strpos($storeUrl, 'index.php') !== false) {
                $storeUrl = str_replace('/index.php', '', $storeUrl);    
            }

            if ($withQuery) {
                if (strpos($storeUrl, '___store=') !== false) {
                    if (($pos = strpos($storeUrl, '?')) !== false) {
                        $qs = substr($storeUrl, $pos+1);

                        if ($qs) {
                            // phpcs:ignore -- parse_str (todo)
                            parse_str($qs, $qsParsed);

                            if (isset($qsParsed['___store'])) {
                                unset($qsParsed['___store']);
                                $storeUrl = substr($storeUrl, 0, $pos);

                                if ($qsParsed) {
                                    $storeUrl .= '?' . http_build_query($qsParsed);
                                } else {
                                    $storeUrl = substr($storeUrl, 0, $pos);
                                }
                            }
                        }
                    }
                }

                $this->currentUrl[$cacheKey] = $storeUrl;
            } else {
                $this->currentUrl[$cacheKey] = preg_replace('/\?.*$/', '', $storeUrl);
            }

            if ($this->isCustomBaseUrl()) {
                $baseUrl = $this->getBaseUrl();
                $activeBaseUrl = $this->getUrl();

                if ($baseUrl !== $activeBaseUrl) {
                    $this->currentUrl[$cacheKey] = str_replace($baseUrl, $activeBaseUrl, $this->currentUrl[$cacheKey]);
                }
            }
        }

        return $this->currentUrl[$cacheKey];
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
        return $this->magentoBaseUrlSource->isEnabled()
            && (string)$this->scopeConfig->getValue(
                'wordpress/setup/custom_base_url'
            ) === \FishPig\WordPress\Model\Config\Source\MagentoBaseUrl::URL_USE_BASE;
    }
}
