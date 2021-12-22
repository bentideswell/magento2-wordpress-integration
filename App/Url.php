<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class Url implements \FishPig\WordPress\Model\UrlInterface
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
        $homeUrl = rtrim($this->homeUrlResolver->getUrl(), '/');

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
        $siteUrl = rtrim($this->siteUrlResolver->getUrl(), '/');
        
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

        return rtrim($this->getHomeUrl(), '/') . '/' . $uri;
    }

    /**
     * Determine whether to use a trailing slash on URLs
     *
     * @return bool
     */
    public function hasTrailingSlash()
    {
        if ($permalinkStructure = $this->option->get('permalink_structure')) {
            return substr($permalinkStructure, -1) === '/';
        }

        return false;
    }

    /**
     * @return bool
     */
    public function doUrlsMatch($url, $url2 = null, $strict = false): bool
    {
        $urls = [
            $url,
            $url2 ?? $this->magentoUrl->getCurrentUrl()
        ];
        
        foreach ($urls as $key => $url) {
            $urls[$key] = rtrim(strtolower($urls[$key]), '/');
        }
        
        if (!$strict) {
            foreach ($urls as $key => $url) {
                $urls[$key] = str_replace(['https://', 'http://'], '', $urls[$key]);
            }
        }
        
        return $urls[0] === $urls[1];
    }

    /**
     * @param  string ...$urls
     * @return bool
     */
    public function doUrlProtocolsMatch(string ...$urls): bool
    {
        $protocol = false;
        
        foreach ($urls as $url) {
            if (false === $protocol) {
                $protocol = substr($url, 0, strpos($url, '://')+3);
            } elseif (strpos($url, $protocol) !== 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * @return string
     */
    public function getBlogRoute(): string
    {
        $magentoUrl = $this->getMagentoUrl();
        $homeUrl = $this->getHomeUrl();
        
        if (strpos($this->getHomeUrl(), $this->getMagentoUrl()) !== 0) {
            throw new \FishPig\WordPress\App\Exception('URLs appear to be invalid.');
        }
        
        return trim(substr($homeUrl, strlen($magentoUrl)), '/');
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
     * @return string
     */
    public function getUploadUrl(): string
    {
        return $this->getWpContentUrl() . 'uploads/';
    }

    /**
     * @return string
     */
    public function getCurrentUrl($withQuery = false): string
    {
        return $this->magentoUrl->getCurrentUrl($withQuery);
    }

    /**
     * @deprecated
     */
    public function getUrl($uri = ''): string
    {
        return $this->getHomeUrl($uri);
    }

    /**
     * @deprecated
     */
    public function getUrlWithFront($uri = ''): string
    {
        return $this->getHomeUrlWithFront($uri);
    }
}
