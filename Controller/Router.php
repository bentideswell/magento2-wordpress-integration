<?php
/**
 *
 */
namespace FishPig\WordPress\Controller;

use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\ActionFactory;
use FishPig\WordPress\Model\IntegrationManager;
use FishPig\WordPress\Model\Url;
use FishPig\WordPress\Model\Factory;
use FishPig\WordPress\Model\Theme;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\App\RequestInterface;
use FishPig\WordPress\Model\Integration\IntegrationException;

class Router implements RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

    /**
     * @var IntegrationManager
     */
    protected $integrationManager;

    /**
     * @var Url
     */
    protected $url;

    /**
     * @var
     */
    protected $callbacks = [];

    /**
     * @var
     */
    protected $routes = [];

    /**
     * @var
     */
    protected $postResourceFactory;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Theme
     */
    protected $theme;
    
    /**
     *
     */
    public function __construct(
        ActionFactory $actionFactory,     
        IntegrationManager $integrationManager,
        Url $url,
        Factory $factory,
        EventManager $eventManager,
        Theme $theme
    ) {
        $this->actionFactory = $actionFactory;
        $this->integrationManager = $integrationManager;
        $this->url = $url;
        $this->factory = $factory;
        $this->eventManager = $eventManager;
        $this->theme = $theme;
    }

    /**
     * @param RequestInterface $request
     */
    public function match(RequestInterface $request)
    {
        if ($this->integrationManager->runTests() === false) {
            return false;
        }


        // If theme not integrated, don't display blog
        if (!$this->theme->isThemeIntegrated()) {
            return false;      
        }

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
            $this->addRouteCallback(array($this, '_getHomepageRoutes'));
        }

        $this->addRouteCallback(array($this, '_getSimpleRoutes'));
        $this->addRouteCallback(array($this, '_getPostRoutes'));
        $this->addRouteCallback(array($this, '_getTaxonomyRoutes'));

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
            foreach($route['params'] as $key => $value) {
                $request->setParam($key, $value);
            }
        }

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }

    /**
     * Execute callbacks and match generated routes against $uri
     *
     * @param string $uri = ''
     * @return false|array
     */
    protected function _matchRoute($uri = '')
    {
        $encodedUri = strtolower(str_replace('----slash----', '/', urlencode(str_replace('/', '----slash----', $uri))));

        foreach($this->callbacks as $callback) {
            $this->routes = [];

            if (call_user_func($callback, $uri, $this) !== false) {
                foreach($this->routes as $route => $data) {
                    $match = false;

                    if (substr($route, 0, 1) !== '/') {
                        $match = $route === $encodedUri || $route === $uri;
                    }
                    else if (preg_match($route, $uri, $matches)) {
                        $match = true;

                        if (isset($data['pattern_keys']) && is_array($data['pattern_keys'])) {
                            array_shift($matches);

                            if (!isset($data['params'])) {
                                $data['params'] = [];
                            }

                            foreach($matches as $match) {
                                if (($pkey = array_shift($data['pattern_keys'])) !== null) {
                                    $data['params'][$pkey] = $match;
                                }
                            }    
                        }
                    }

                    if ($match) {
                        if (isset($data['params']['__redirect_to'])) {
                            header('Location: ' . $data['params']['__redirect_to']);
                            exit;
                        }

                        return $data;
                    }
                }    
            }
        }

        return false;
    }

    /**
     * Add a generated route and it's details
     *
     * @param array|string $pattern
     * @param string $path
     * @param array|null $params = array()
     * @return $this
     */
    public function addRoute($pattern, $path, $params = array())
    {
        if (is_array($pattern)) {
            $keys = $pattern[key($pattern)];
            $pattern = key($pattern);
        }
        else {
            $keys = [];
        }

        $path = array_combine(array('module', 'controller', 'action'), explode('/', $path));

        if ($path['module'] === '*') {
            $path['module'] = 'wordpress';
        }

        $this->routes[$pattern] = array(
            'path' => $path,
            'params' => $params,
            'pattern_keys' => $keys,
        );

        return $this;
    }

    /**
     * Add a callback method to generate new routes
     *
     * @param array
     */
    public function addRouteCallback(array $callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Get route data for different homepage URLs
     *
     * @param string $uri = ''
     * @return $this
     */
    protected function _getHomepageRoutes($uri = '')
    {
        $homepage = $this->factory->get('FishPig\WordPress\Model\Homepage');

        if (!$uri) {
          $keys = ['page_id', 'post_id', 'p'];

          foreach($keys as $key) {
            if ($postId = (int)$this->request->getParam($key)) {
              break;
            }
          }

          if ($postId) {
            $paramKeys = strtolower(implode('-', array_keys($this->request->getParams())));

            if (strpos($paramKeys, 'preview') !== false || strpos($paramKeys, 'vc_editable') !== false) {
          return $this->addRoute('', '*/post/view', ['id' => $postId]);
            }
          }
        }

        if ($frontPageId = $homepage->getFrontPageId()) {
            $this->addRoute('', '*/post/view', ['id' => $frontPageId, 'is_front' => 1]);
        }
        else {
            $this->addRoute('', '*/homepage/view');
        }

        return $this;
    }

    /**
     * Generate the basic simple routes that power WP
     *
     * @param string $uri = ''
     * @return false|$this
     */    
    protected function _getSimpleRoutes($uri = '')
    {
        if ($front = $this->url->getFront()) {
            $front .= '\/';
        }

        $this->addRoute(array('/^' . $front . 'author\/([^\/]{1,})$/' => array('author')), '*/user/view');
        $this->addRoute(array('/^' . $front . '([1-2]{1}[0-9]{3})$/' => array('year')), '*/archive/view');
        $this->addRoute(array('/^' . $front . '([1-2]{1}[0-9]{3})\/([0-1]{1}[0-9]{1})$/' => array('year', 'month')), '*/archive/view');
        $this->addRoute(array('/^' . $front . '([1-2]{1}[0-9]{3})\/([0-1]{1}[0-9]{1})\/([0-3]{1}[0-9]{1})$/' => array('year', 'month', 'day')), '*/archive/view');
        $this->addRoute(array('/^' . $front . 'search\/(.*)$/' => array('s')), '*/search/view');
        $this->addRoute('search', '*/search/index', array('redirect_broken_url' => 1)); # Fix broken search URLs
#        $this->addRoute('/^index.php/i', '*/index/forward');

        $this->addRoute(['/^((newbloguser|wp-(content|includes|admin|cron\.php))\/.*)$/' => ['request_uri']], '*/forwarder/view');

#        $this->addRoute('/^wp-content\/(.*)/i', '*/index/forwardFile');
#        $this->addRoute('/^wp-includes\/(.*)/i', '*/index/forwardFile');
#        $this->addRoute('/^wp-cron.php.*/', '*/index/forwardFile');
#        $this->addRoute('/^wp-admin[\/]{0,1}$/', '*/index/wpAdmin');
#        $this->addRoute('/^wp-pass.php.*/', '*/index/applyPostPassword');
#        $this->addRoute('robots.txt', '*/index/robots');
        $this->addRoute('comments', '*/index/commentsFeed');

        $this->addRoute(array('/^wp-json$/' => []), '*/json/view');
        $this->addRoute(array('/^wp-json\/(.*)$/' => array('json_route_data')), '*/json/view');

        return $this;
    }

    /**
     * Generate the post routes
     *
     * @param string $uri = ''
     * @return false|$this
     */
    protected function _getPostRoutes($uri = '')
    {
        if (($routes = $this->factory->get('FishPig\WordPress\Model\ResourceModel\Post')->getPermalinksByUri($uri)) === false) {
            return false;
        }

        $pageForPostsId = (int)$this->factory->get('FishPig\WordPress\Model\Homepage')->getPageForPostsId();

        foreach($routes as $routeId => $route) {
            if ($pageForPostsId && $pageForPostsId === (int)$routeId) {
                $this->addRoute(trim($route, '/'), '*/homepage/view', array('id' => $routeId));
            }
            else {
                $this->addRoute(trim($route, '/'), '*/post/view', array('id' => $routeId));
            }
        }

        return $this;
    }

    /**
     * Get the custom taxonomy URI's
     * First check whether a valid taxonomy exists in $uri
     *
     * @param string $uri = ''
     * @return $this
     */
    protected function _getTaxonomyRoutes($uri = '')
    {
        foreach($this->factory->get('FishPig\WordPress\Model\TaxonomyManager')->getTaxonomies() as $taxonomy) {
            if (($routes = $taxonomy->getUris($uri)) !== false) {
                foreach($routes as $routeId => $route) {
                    $this->addRoute($route, '*/term/view', array('id' => $routeId, 'taxonomy' => $taxonomy->getTaxonomyType()));
                }
            }
        }

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function addExtraRoutesToQueue()
    {
        return $this;
    }

    /**
     *
     *
     * @return string
     */
    public function getPathInfo(RequestInterface $request)
    {
        $pathInfo = strtolower(trim($request->getOriginalPathInfo(), '/'));

        if ($magentoUrlPath = parse_url($this->url->getMagentoUrl(), PHP_URL_PATH)) {
            $magentoUrlPath = ltrim($magentoUrlPath, '/');

            if (strpos($pathInfo, $magentoUrlPath) === 0) {
                $pathInfo = ltrim(substr($pathInfo, strlen($magentoUrlPath)), '/');
            }
        }

        return $pathInfo;
    }

    /**
     *
     *
     * @return 
     */
    public function getUrlAlias(RequestInterface $request)
    {
        $pathInfo = $this->getPathInfo($request);
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

        /**
        // Clean off feed and trackback variable
        if (($key = array_search('feed', $pathInfo)) !== false) {
            unset($pathInfo[$key]);

            if (isset($pathInfo[$key+1])) {
                unset($pathInfo[$key+1]);
            }

            $request->setParam('feed', 'rss2');
            $request->setParam('feed_type', 'rss2');
        }
        */

        // Remove comments pager variable
        foreach($pathInfo as $i => $part) {
            $results = array();
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

        return $uri;
    }

    /**
     * Retrieve the blog URI
     * This is the whole URI after blog route
     *
     * @return string
     */
    public function getRouterRequestUri(RequestInterface $request)
    {
        if (($alias = $this->getUrlAlias($request)) !== false) {
            if ($blogRoute = $this->url->getBlogRoute()) {
                return strpos($alias . '/', $blogRoute .'/') === 0 ? ltrim(substr($alias, strlen($blogRoute)), '/') : false;
            }

            return $alias;
        }

        return false;
    }
}
