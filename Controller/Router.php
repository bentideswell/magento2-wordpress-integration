<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller;

use \Magento\Framework\App\RequestInterface;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @auto
     */
    protected $integrationTests = null;

    /**
     * @auto
     */
    protected $routerUrlHelper = null;

    /**
     * @auto
     */
    protected $requestDispatcher = null;

    /**
     * @var []
     */
    private $routerPool = [];

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Tests\Proxy $integrationTests,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        array $routerPool = []
    ) {
        $this->integrationTests = $integrationTests;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->requestDispatcher = $requestDispatcher;
        $this->routerPool = $routerPool;
    }

    /**
     * @param RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        try {
            if ($this->integrationTests->runTests() === false) {
                return false;
            }
        } catch (IntegrationFatalException $e) {
            throw $e;
        } catch (\FishPig\WordPress\App\Exception $e) {
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
                if ($redirectUrl = $this->routerUrlHelper->getRedirectUrlBasedOnTrailingSlash($request)) {
                    return $this->requestDispatcher->redirect(
                        $request,
                        $redirectUrl,
                        301
                    );
                }

                return $result;
            }
        }

        return false;
    }
}
