<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

abstract class AbstractItemProvider implements \Magento\Sitemap\Model\ItemProvider\ItemProviderInterface
{
    /**
     *
     */
    private $storeIdUrlMap = [];

    /**
     *
     */
    protected $logger = null;

    /**
     *
     */
    public function __construct(
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $itemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Logger $logger
    ) {
        $this->itemFactory = $itemFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     *
     */
    protected function makeUrlRelative(string $url): string
    {
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();

        if (!isset($this->storeIdUrlMap[$storeId])) {
            // Store the URL without the protocol.
            // This allows us to remove it using both protocols below
            // This catches an issue where some stores have http hidden in the config
            $this->storeIdUrlMap[$storeId] = preg_replace(
                '/^http(s)?:\/\//i',
                '',
                rtrim($store->getBaseUrl(), '/')
            );
        }

        // Remove base URL as Magento wants relative URLs
        // This removes base URL with http and https
        $url = ltrim(
            str_replace(
                [
                    'https://' . $this->storeIdUrlMap[$storeId],
                    'http://' . $this->storeIdUrlMap[$storeId]
                ],
                '',
                $url
            ),
            '/'
        );

        return $url;
    }


    /**
     * This could be useful for plugins
     * $item may be a URL or a post, it's different for item provider
     *
     * @return bool
     */
    public function canAddToSitemap($item): bool
    {
        return true;
    }

    /**
     * This could be useful for plugins
     * $item may be a URL or a post, it's different for item provider
     *
     * @return float
     */
    public function getPriority($item): float
    {
        return static::PRIORITY;
    }

    /**
     * This could be useful for plugins
     * $item may be a URL or a post, it's different for item provider
     *
     * @return string
     */
    public function getChangeFrequency($item): string
    {
        return static::CHANGE_FREQUENCY;
    }
}
