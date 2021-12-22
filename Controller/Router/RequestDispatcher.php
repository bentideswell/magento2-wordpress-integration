<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

use Magento\Framework\App\RequestInterface;
use \Magento\Framework\App\ActionInterface;

class RequestDispatcher
{
    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory,
     * @param \FishPig\WordPress\App\Url\Router $routerUrlHelper
     */
    public function __construct(
        \Magento\Framework\App\ResponseInterface $response,
        \Magento\Framework\App\ActionFactory $actionFactory,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper
    ) {
        $this->response = $response;
        $this->actionFactory = $actionFactory;
        $this->routerUrlHelper = $routerUrlHelper;
    }

    /**
     * @param  RequestInterface $request
     * @param  string $route
     * @param  array $params = []
     * @return ActionInterface
     */
    public function dispatch(RequestInterface $request, string $route, array $params = []): ActionInterface
    {
        $routeParts = $this->parseRoute($route);

        $request->setModuleName(
            $routeParts['module']
        )->setControllerName(
            $routeParts['controller']
        )->setActionName(
            $routeParts['action']
        )->setAlias(
            \Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
            $this->routerUrlHelper->getUrlAlias($request)
        );

        if ($params) {
            $request->setParams($params);
        }

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Forward::class
        );
    }

    /**
     * @param  RequestInterface $request
     * @param  string $url
     * @param  int $code = 302
     * @return ActionInterface
     */
    public function redirect(RequestInterface $request, string $url, int $code = 302): ActionInterface
    {
        $this->response->setRedirect($url, $code);
        $request->setDispatched(true);

        return $this->actionFactory->create(
            \Magento\Framework\App\Action\Redirect::class
        );
    }

    /**
     * @param string $route
     * @return []
     */
    private function parseRoute(string $route): array
    {
        $routeParts = explode('/', $route);

        if (count($routeParts) !== 3) {
            throw new \FishPig\WordPress\App\Exception('Invalid route (' . $route . ') supplied to router.');
        }

        return [
            'module' => $routeParts[0] === '*' ? 'wordpress' : $routeParts[0],
            'controller' => $routeParts[1],
            'action' => $routeParts[2]
        ];
    }
}
