<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Url
{
    /**
     * @var []
     */
    private $front = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Url\SiteUrlResolver $siteUrlResolver,
        \FishPig\WordPress\App\Url\HomeUrlResolver $homeUrlResolver,
        \FishPig\WordPress\App\Url\MagentoUrl $magentoUrl,
        \FishPig\WordPress\App\Option $option,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->siteUrlResolver = $siteUrlResolver;
        $this->homeUrlResolver = $homeUrlResolver;
        $this->magentoUrl = $magentoUrl;
        $this->option = $option;
        $this->storeManager = $storeManager;
    }

    /**
     * @param  $uri = ''
     * @return string
     */
    public function getHomeUrl($uri = ''): string
    {
        $homeUrl = rtrim($this->homeUrlResolver->resolve()->getUrl(), '/');
        $homeUrl = 'https://m2wp.local.fishpig.com/blog';

        if ($uri) {
            $homeUrl .= '/' . $uri;
        }
        
        if (($queryPos = strpos($homeUrl, '?')) !== false) {
            $lastChar = substr($homeUrl, $queryPos-1, 1);
        } else {
            $lastChar = substr($homeUrl, -1, 1);
        }

        if ($this->hasTrailingSlash()) {
            if ($lastChar !== '/') {
                if ($queryPos) {
                    $homeUrl = substr($homeUrl, 0, $queryPos) . '/' . substr($homeUrl, $queryPos);
                } else {
                    $homeUrl .= '/';
                }
            }
        } elseif ($lastChar === '/') {
            if ($queryPos) {
                $homeUrl = substr($homeUrl, 0, $queryPos-1) . substr($homeUrl, $queryPos);
            } else {
                $homeUrl = rtrim($homeUrl, '/');
            }
        }
        
        return $homeUrl;
    }

    /**
     * @param  $uri = ''
     * @return string
     */
    public function getSiteUrl($uri = ''): string
    {
        $siteUrl = rtrim($this->siteUrlResolver->resolve()->getUrl(), '/');
        
        if ($uri) {
            $siteUrl .= '/' . $uri;
        }
        
        return $siteUrl;
    }

    /**
     * @return string
     */
    public function getRestUrl($uri = ''): string
    {
        return $this->getSiteUrl('index.php?rest_route=' . $uri);
    }

    /**
     * @return string
     */
    public function getMagentoUrl(): string
    {
        return rtrim($this->magentoUrl->getUrl(), '/');
    }
    
    /**
     * @return string
     */
    public function getFront(): string
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->front[$storeId])) {
            $this->front[$storeId] = '';

            if ($this->isRoot()) {
                $postPermalink = $this->option->get('permalink_structure');

                if (substr($postPermalink, 0, 1) !== '%') {
                    $this->front[$storeId] = trim(substr($postPermalink, 0, strpos($postPermalink, '%')), '/');
                }
            }
        }

        return $this->front[$storeId];
    }

    /**
     * Generate a WordPress frontend URL with the Front var in it
     *
     * @param  string $uri = ''
     * @return string
     */
    public function getHomeUrlWithFront($uri = ''): string
    {
        if ($front = $this->getFront()) {
            $uri = ltrim($front . '/' . $uri, '/');
        }

        return $this->getHomeUrl() . '/' . $uri;
    }

    /**
     * Determine whether to use a trailing slash on URLs
     *
     * @return bool
     */
    private function hasTrailingSlash()
    {
        if ($permalinkStructure = $this->option->get('permalink_structure')) {
            return substr($permalinkStructure, -1) === '/';
        }

        return false;
    }

    /**
     * @return string
     */
    public function getBlogRoute(): string
    {
        return trim(substr($this->getHomeUrl(), strlen($this->getMagentoUrl())), '/');
    }

    /**
     * @return bool
     */
    public function isRoot(): bool
    {
        return false;
    }
    
    /**
     * @return string
     */    
    public function getWpContentUrl(): string
    {
        return $this->getSiteUrl('wp-content/');
    }
    
    /**
     * @param  $uri = ''
     * @return string
     */
    public function getUrl($uri = ''): string
    {
        return $this->getHomeUrl($uri);
    }
}
