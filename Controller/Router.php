<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller;

use \Magento\Framework\App\RequestInterface;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var []
     */
    private $routers = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Tests $integrationTests,
        \FishPig\WordPress\App\Url\Router $routerUrlHelper,
        array $routers = []
    ) {
        $this->integrationTests = $integrationTests;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->routers = $routers;
    }

    /**
     * @param RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if ($this->integrationTests->runTests() === false) {
            return false;
        }

        if (!$this->routerUrlHelper->isRequestServiceable($request)) {
            echo __LINE__;exit;
            return false;
        }

        foreach ($this->routers as $router) {
            if (!($router instanceof \Magento\Framework\App\RouterInterface)) {
                continue;
            }

            if (($result = $router->match($request)) !== false) {
                return $result;
            }
        }
                    echo __LINE__;exit;
        return false;
        // If theme not integrated, don't display blog
//        if (!$this->theme->isThemeIntegrated()) {
//            return false;
//        }
        
        $this->request  = $request;
        $fullRequestUri = $this->getPathInfo($request);
        $blogRoute      = $this->url->getBlogRoute();

        if ($blogRoute && ($blogRoute !== $fullRequestUri && strpos($fullRequestUri, $blogRoute . '/') !== 0)) {
            return false;
        }

        $this->eventManager->dispatch(
            'wordpress_router_match_before',
            ['router' => $this, 'blog_route' => $blogRoute, 'full_request_uri' => $fullRequestUri]
        );

        if (!($requestUri = $this->getRouterRequestUri($request))) {
            $this->addRouteCallback([$this, '_getHomepageRoutes']);
        }

        $this->addRouteCallback([$this, '_getSimpleRoutes']);
        $this->addRouteCallback([$this, '_getPostRoutes']);
        $this->addRouteCallback([$this, '_getTaxonomyRoutes']);

        $this->addExtraRoutesToQueue();

        if (($route = $this->_matchRoute($requestUri)) === false) {
            return false;
        }

        $request->setModuleName($route['path']['module'])
            ->setControllerName($route['path']['controller'])
            ->setActionName($route['path']['action'])
            ->setAlias(
                \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
                $this->getUrlAlias($request)
            );

        if (count($route['params']) > 0) {
            foreach ($route['params'] as $key => $value) {
                $request->setParam($key, $value);
            }
        }

        return $this->actionFactory->create(\Magento\Framework\App\Action\Forward::class);
    }

    /**
     * Generate the basic simple routes that power WP
     *
     * @param  string $uri = ''
     * @return false|$this
     */
    protected function _getSimpleRoutes($uri = '')
    {
        if ($front = $this->url->getFront()) {
            $front = preg_quote($front . '/', '/');
        }

        $this->addRoute(['/^' . $front . 'author\/([^\/]{1,})$/' => ['author']], '*/user/view');
        $this->addRoute(['/^' . $front . '([1-2]{1}[0-9]{3})$/' => ['year']], '*/archive/view');
        $this->addRoute(['/^' . $front . '([1-2]{1}[0-9]{3})\/([0-1]{1}[0-9]{1})$/' => ['year', 'month']], '*/archive/view');
        $this->addRoute(['/^' . $front . '([1-2]{1}[0-9]{3})\/([0-1]{1}[0-9]{1})\/([0-3]{1}[0-9]{1})$/' => ['year', 'month', 'day']], '*/archive/view');
        $this->addRoute(['/^' . $front . 'search\/(.*)$/' => ['s']], '*/search/view');
        $this->addRoute('search', '*/search/index', ['redirect_broken_url' => 1]); // Fix broken search URLs
        // $this->addRoute('/^index.php/i', '*/index/forward');

        $this->addRoute(['/^((newbloguser|wp-(content|includes|admin|cron\.php))\/.*)$/' => ['request_uri']], '*/forwarder/view');

        // $this->addRoute('/^wp-content\/(.*)/i', '*/index/forwardFile');
        // $this->addRoute('/^wp-includes\/(.*)/i', '*/index/forwardFile');
        // $this->addRoute('/^wp-cron.php.*/', '*/index/forwardFile');
        // $this->addRoute('/^wp-admin[\/]{0,1}$/', '*/index/wpAdmin');

        // $this->addRoute('robots.txt', '*/index/robots');
        $this->addRoute('comments', '*/index/commentsFeed');

        $this->addRoute(['/^wp-json$/' => []], '*/json/view');
        $this->addRoute(['/^wp-json\/(.*)$/' => ['json_route_data']], '*/json/view');

        return $this;
    }

    /**
     * Generate the post routes
     *
     * @param  string $uri = ''
     * @return false|$this
     */
    protected function _getPostRoutes($uri = '')
    {
        if (($routes = $this->factory->get(\FishPig\WordPress\Model\ResourceModel\Post::class)->getPermalinksByUri($uri)) === false) {
            return false;
        }

        $pageForPostsId = (int)$this->factory->get(\FishPig\WordPress\Model\Homepage::class)->getPageForPostsId();

        foreach ($routes as $routeId => $route) {
            if ($pageForPostsId && $pageForPostsId === (int)$routeId) {
                $this->addRoute(trim($route, '/'), '*/homepage/view', ['id' => $routeId]);
            } else {
                $this->addRoute(trim($route, '/'), '*/post/view', ['id' => $routeId]);
            }
        }

        return $this;
    }

    /**
     * Get the custom taxonomy URI's
     * First check whether a valid taxonomy exists in $uri
     *
     * @param  string $uri = ''
     * @return $this
     */
    protected function _getTaxonomyRoutes($uri = '')
    {
        foreach ($this->factory->get(\FishPig\WordPress\Model\TaxonomyManager::class)->getTaxonomies() as $taxonomy) {
            if (($routes = $taxonomy->getUris($uri)) !== false) {
                foreach ($routes as $routeId => $route) {
                    if ($route === $uri) {
                        return $this->addRoute($route, '*/term/view', ['id' => $routeId, 'taxonomy' => $taxonomy->getTaxonomyType()]);
                    }
                }
            }

            if (($routes = $taxonomy->getRedirectableUris($uri)) !== false) {
                foreach ($routes as $routeId => $route) {
                    if ($uri === $route['source']) {
                        return $this->addRoute(
                            $route['source'],
                            '*/term/view',
                            [
                            'id' => $routeId,
                            'taxonomy' => $taxonomy->getTaxonomyType(),
                            '__redirect_to' => $this->url->getUrl($route['target'])
                            ]
                        );
                    }
                }
            }
        }

        return $this;
    }
}
