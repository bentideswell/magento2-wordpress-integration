<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

class SimpleRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper
    ) {
        $this->requestDispatcher = $requestDispatcher;
        $this->routerUrlHelper = $routerUrlHelper;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $pathInfo = $this->routerUrlHelper->getRelativePathInfo($request);

        foreach ($this->getRegexRoutePatterns() as $pattern => $routePath) {
            if (preg_match($pattern, $pathInfo, $matches)) {
                return $this->requestDispatcher->dispatch(
                    $request,
                    $routePath,
                    $this->findParamsInMatchesArray($matches)
                );
            }
        }
        
        return false;
    }

    /**
     * @param  array $matches
     * @return array
     */
    private function findParamsInMatchesArray(array $matches): array
    {
        foreach ($matches as $key => $value) {
            if (is_numeric($key)) {
                unset($matches[$key]);
            }
        }

        return $matches;
    }

    /**
     * @return array
     */
    private function getRegexRoutePatterns(): array
    {
        $front = $this->getPregQuotedFront();
        $yearRegex = '(?P<year>[1-2]{1}[0-9]{3})';
        $monthRegex = '(?P<month>[0-1]{1}[0-9]{1})';
        $dayRegex = '(?P<day>[0-3]{1}[0-9]{1})';
        
        return [
            '/^' . $front . 'author\/(?P<author>[^\/]+)$/' => '*/user/view',
            '/^' . $front . "{$yearRegex}$/" => '*/archive/view',
            '/^' . $front . "{$yearRegex}\/{$monthRegex}$/" => '*/archive/view',
            '/^' . $front . "{$yearRegex}\/$monthRegex\/$dayRegex$/" => '*/archive/view',
            '/^' . $front . 'search\/(?P<s>.*)$/' => '*/search/view',
            '/^' . $front . 'search$/' => '*/search/index',
            '/^(?P<request_uri>(newbloguser|wp-(content|includes|admin|cron\.php))\/.*)$/' => '*/forwarder/view',
            '/^wp-json$/' => '*/jsonApi/view',
            '/^wp-json(?P<json_route_data>.*)$/' => '*/jsonApi/view',
        ];
    }
    
    /**
     * @return string
     */
    private function getPregQuotedFront(): string
    {
        return preg_quote(ltrim($this->routerUrlHelper->getFront() . '/', '/'), '/');
    }
}
