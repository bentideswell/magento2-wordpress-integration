<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

class HomepageRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Helper\FrontPage $frontPage
    ) {
        $this->requestDispatcher = $requestDispatcher;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->frontPage = $frontPage;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        if (($pathInfo = $this->routerUrlHelper->getRelativePathInfo($request)) !== '') {
            return false;
        }

        $paramKeys = strtolower(implode('-', array_keys($request->getParams())));
        
        foreach ($this->getQueryParamsForIds() as $key) {
            if ($postId = (int)$request->getParam($key)) {
                if (strpos($paramKeys, 'preview') !== false || strpos($paramKeys, 'vc_editable') !== false) {
                    return $this->requestDispatcher->dispatch($request, '*/post/view', ['id' => $postId]);
                }
            }
        }

        if ($frontPageId = $this->frontPage->getFrontPageId()) {
            return $this->requestDispatcher->dispatch(
                $request,
                '*/post/view',
                ['id' => $frontPageId]
            );
        }
            
        return $this->requestDispatcher->dispatch($request, '*/postType/view', ['post_type' => 'post']);
    }
    
    /**
     * @return []
     */
    private function getQueryParamsForIds(): array
    {
        return [
            'page_id',
            'post_id',
            'p'
        ];
    }
}
