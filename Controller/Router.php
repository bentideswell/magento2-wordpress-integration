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
    private $routerPool = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Tests $integrationTests,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        array $routerPool = []
    ) {
        $this->integrationTests = $integrationTests;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->routerPool = $routerPool;
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
            return false;
        }

        foreach ($this->routerPool as $routerId => $router) {
            if (!($router instanceof \Magento\Framework\App\RouterInterface)) {
                continue;
            }

            if (($result = $router->match($request)) !== false) {
                return $result;
            }
        }
        echo 'No router match in ' . __METHOD__;
        exit;
        return false;


        $this->addRouteCallback([$this, '_getTaxonomyRoutes']);

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
