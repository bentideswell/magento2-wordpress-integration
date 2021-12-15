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

        return false;
    }
}
