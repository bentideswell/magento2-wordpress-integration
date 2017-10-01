<?php
/**
 * Copyright © 2016 FishPig. All rights reserved.
 */
namespace FishPig\WordPress\Controller;

use Magento\Framework\App\RequestInterface;

class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var ActionFactory
     */
    protected $actionFactory;

	/**
	 * @var FishPig\WordPress\Model\App
	 **/
	protected $_app;
	
	/**
	 * @var FishPig\WordPress\Model\App\Url
	 **/
	protected $_wpUrlBuilder;
	
	/**
	 * @var array
	 */
	protected $_callbacks = array();
	
	/**
	 * @var array
	 */
	protected $_routes = array();
	
	/**
	 * @var
	 */
	protected $_postResourceFactory = null;
	
    /**
     * @param ActionFactory $actionFactory
     * @param NoRouteHandlerList $noRouteHandlerList
     */
    public function __construct(
    	\Magento\Framework\App\ActionFactory $actionFactory, 	
    	\FishPig\WordPress\Model\App $app,
    	\FishPig\WordPress\Model\App\Url $urlBuilder,
    	\FishPig\WordPress\Model\ResourceModel\PostFactory $postResourceFactory,
    	\Magento\Framework\App\Request\Http $request
    )
    {
        $this->actionFactory = $actionFactory;
        $this->_app = $app;
        $this->_wpUrlBuilder = $urlBuilder;
        $this->_postResourceFactory = $postResourceFactory;
        $this->request = $request;
    }

    /**
     * @param RequestInterface $request
     */
	public function match(RequestInterface $request)
	{	
		try {
			if (!$this->_app->canRun()) {
				return false;
			}

			$fullRequestUri = $this->_wpUrlBuilder->getPathInfo($request);
			$blogRoute = $this->_wpUrlBuilder->getBlogRoute();

			if ($blogRoute && ($blogRoute !== $fullRequestUri && strpos($fullRequestUri, $blogRoute . '/') !== 0)) {
				return false;
			}

			if (!($requestUri = $this->_wpUrlBuilder->getRouterRequestUri($request))) {
				$this->addRouteCallback(array($this, '_getHomepageRoutes'));	
			}

			$this->addRouteCallback(array($this, '_getSimpleRoutes'));
			$this->addRouteCallback(array($this, '_getPostRoutes'));
			$this->addRouteCallback(array($this, '_getTaxonomyRoutes'));
			
			$this->addExtraRoutesToQueue();
			
			if (($route = $this->_matchRoute($requestUri)) !== false) {
				$request->setModuleName($route['path']['module'])
					->setControllerName($route['path']['controller'])
					->setActionName($route['path']['action'])
					->setAlias(
						\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS,
						$this->_wpUrlBuilder->getUrlAlias($request)
					);
				
				if (count($route['params']) > 0) {
					foreach($route['params'] as $key => $value) {
						$request->setParam($key, $value);
					}
				}

				return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
			}
		}
		catch (\Exception $e) {
			throw $e;
		}
		
		return false;
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
		
		foreach($this->_callbacks as $callback) {
			$this->_routes = array();

			if (call_user_func($callback, $uri, $this) !== false) {
				foreach($this->_routes as $route => $data) {
					$match = false;

					if (substr($route, 0, 1) !== '/') {
						$match = $route === $encodedUri || $route === $uri;
					}
					else {
						if (preg_match($route, $uri, $matches)) {
							$match = true;
							
							if (isset($data['pattern_keys']) && is_array($data['pattern_keys'])) {
								array_shift($matches);
								
								if (!isset($data['params'])) {
									$data['params'] = array();
								}

								foreach($matches as $match) {
									if (($pkey = array_shift($data['pattern_keys'])) !== null) {
										$data['params'][$pkey] = $match;
									}
								}	
							}
						}
					}
					
					if ($match) {
						if (isset($data['params']['__redirect_to'])) {
							header('Location: ' . $data['params']['__redirect_to']);
							exit;	
						}

/*
						if ($this->request->getParam('preview') === 'true') {
							$data['path']['controller'] = 'post';
							$data['path']['action'] = 'preview';
							$data['params'] = array(
								'p' => (int)$this->request->getParam('p')
							);
						}
						*/

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
			$keys = array();
		}

		$path = array_combine(array('module', 'controller', 'action'), explode('/', $path));
		
		if ($path['module'] === '*') {
			$path['module'] = 'wordpress';
		}

		$this->_routes[$pattern] = array(
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
		$this->_callbacks[] = $callback;
		
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
#		if ($postId = $this->getRequest()->getParam('p')) {
#			return $this->addRoute('', '*/post/view', array('p' => $postId, 'id' => $postId));
#		}

#		if (($pageId = $this->_getHomepagePageId()) !== false) {
#			return $this->addRoute('', '*/post/view', array('id' => $pageId, 'post_type' => 'page', 'home' => 1));
#		}

		$this->addRoute('', '*/homepage/view');
		
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
		$this->addRoute(array('/^author\/([^\/]{1,})/' => array('author')), '*/user/view');
		$this->addRoute(array('/^([1-2]{1}[0-9]{3})$/' => array('year')), '*/archive/view');
		$this->addRoute(array('/^([1-2]{1}[0-9]{3})\/([0-1]{1}[0-9]{1})$/' => array('year', 'month')), '*/archive/view');
		$this->addRoute(array('/^([1-2]{1}[0-9]{3})\/([0-1]{1}[0-9]{1})\/([0-3]{1}[0-9]{1})$/' => array('year', 'month', 'day')), '*/archive/view');
		$this->addRoute(array('/^search\/(.*)$/' => array('s')), '*/search/view');
		$this->addRoute('search', '*/search/index', array('redirect_broken_url' => 1)); # Fix broken search URLs
#		$this->addRoute('/^index.php/i', '*/index/forward');
#		$this->addRoute('/^wp-content\/(.*)/i', '*/index/forwardFile');
#		$this->addRoute('/^wp-includes\/(.*)/i', '*/index/forwardFile');
#		$this->addRoute('/^wp-cron.php.*/', '*/index/forwardFile');
#		$this->addRoute('/^wp-admin[\/]{0,1}$/', '*/index/wpAdmin');
#		$this->addRoute('/^wp-pass.php.*/', '*/index/applyPostPassword');
#		$this->addRoute('robots.txt', '*/index/robots');
		$this->addRoute('comments', '*/index/commentsFeed');
		$this->addRoute(array('/^newbloguser\/(.*)$/' => array('code')), '*/index/forwardNewBlogUser');

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
		if (($routes = $this->_postResourceFactory->create()->getPermalinksByUri($uri)) === false) {
			return false;
		}

		foreach($routes as $routeId => $route) {
			$this->addRoute(rtrim($route, '/'), '*/post/view', array('id' => $routeId));
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
		foreach($this->_app->getTaxonomies() as $taxonomy) {
			if (($routes = $taxonomy->getUris($uri)) !== false) {
				foreach($routes as $routeId => $route) {
					$this->addRoute($route, '*/term/view', array('id' => $routeId, 'taxonomy' => $taxonomy->getTaxonomyType()));
					$this->addRoute(rtrim($route, '/') . '/feed', '*/term/feed', array('id' => $routeId, 'taxonomy' => $taxonomy->getTaxonomyType()));
					
					if ($taxonomy->getExtraRoutes()) {
						foreach($taxonomy->getExtraRoutes() as $pattern => $newRoute) {
							$this->addRoute(str_replace('*', $route, $pattern), $newRoute, array('id' => $routeId, 'taxonomy' => $taxonomy->getTaxonomyType()));
						}
					}
				}
			}
		}

		return $this;
	}
	
	/**
	 *
	 * @return $this
	**/
	public function addExtraRoutesToQueue()
	{
		return $this;
	}
}
