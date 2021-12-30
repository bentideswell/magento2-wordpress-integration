<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

use \Magento\Framework\App\RequestInterface;

class UrlHelper
{
    /**
     * @var []
     */
    private $cache = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Url $url
    ) {
        $this->url = $url;
    }

    /**
     * @param  RequestInterface $request
     * @return bool
     */
    public function isRequestServiceable(RequestInterface $request): bool
    {
        if (!($blogRoute = $this->url->getBlogRoute())) {
            // Blog is available from root so any request could be a match
            return true;
        }

        if (strpos($this->getPathInfo($request) . '/', $blogRoute . '/') === 0) {
            // Current request starts with blog route
            return true;
        }

        return false;
    }

    /**
     * @param  RequestInterface $request
     * @return string|false
     */
    public function getRelativePathInfo(RequestInterface $request)
    {
        if (($alias = $this->getUrlAlias($request)) !== false) {
            if ($blogRoute = $this->url->getBlogRoute()) {
                if (strpos($alias . '/', $blogRoute .'/') === 0) {
                    return ltrim(substr($alias, strlen($blogRoute)), '/');
                }
                
                return false;
            }

            return $alias;
        }

        return false;
    }

    /**
     * @param  RequestInterface $request
     * @return string|false
     */
    public function getUrlAlias(RequestInterface $request)
    {
        $pathInfo = $this->getPathInfo($request);

        // phpcs:ignore -- not cryptographic
        $cacheKey = md5('alias:' . $pathInfo);
        
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }
        
        $blogRoute = $this->url->getBlogRoute();

        if ($blogRoute && strpos($pathInfo, $blogRoute) !== 0) {
            return false;
        }

        if (trim(substr($pathInfo, strlen($blogRoute)), '/') === '') {
            return $pathInfo;
        }

        $pathInfo = explode('/', $pathInfo);

        // Clean off pager
        if (($key = array_search('page', $pathInfo)) !== false) {
            if (isset($pathInfo[($key+1)]) && preg_match("/[0-9]{1,}/", $pathInfo[($key+1)])) {
                $request->setParam('page', $pathInfo[($key+1)]);
                unset($pathInfo[($key+1)]);
                unset($pathInfo[$key]);

                $pathInfo = array_values($pathInfo);
            }
        }

        // Remove comments pager variable
        foreach ($pathInfo as $i => $part) {
            $results = [];
            if (preg_match("/" . sprintf('^comment-page-%s$', '([0-9]{1,})') . "/", $part, $results)) {
                if (isset($results[1])) {
                    unset($pathInfo[$i]);
                }
            }
        }

        if (count($pathInfo) == 1 && preg_match("/^[0-9]{1,8}$/", $pathInfo[0])) {
            $request->setParam('p', $pathInfo[0]);

            array_shift($pathInfo);
        }

        $uri = urldecode(implode('/', $pathInfo));

        return $this->cache[$cacheKey] = $uri;
    }

    /**
     * @return string
     */
    public function getFront(): string
    {
        return $this->url->getFront();
    }
    
    /**
     * @param  RequestInterface $request
     * @return string
     */
    private function getPathInfo(RequestInterface $request): string
    {
        $pathInfo = strtolower(trim($request->getOriginalPathInfo(), '/'));

        // phpcs:ignore -- parse_url
        if ($magentoUrlPath = parse_url($this->url->getMagentoUrl(), PHP_URL_PATH)) {
            $magentoUrlPath = ltrim($magentoUrlPath, '/');

            if (strpos($pathInfo, $magentoUrlPath) === 0) {
                // ToDo
                // This was removed due to conflicts with WPML
                // It may need to be reviewed and added back in
                // $pathInfo = ltrim(substr($pathInfo, strlen($magentoUrlPath)), '/');
            }
        }

        return $pathInfo;
    }
}
