<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

class PostRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Model\ResourceModel\Post\Permalink $permalinkResource
    ) {
        $this->requestDispatcher = $requestDispatcher;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->permalinkResource = $permalinkResource;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $postId = $this->permalinkResource->getPostIdByPathInfo(
            $this->routerUrlHelper->getRelativePathInfo($request)
        );

        if ($postId !== false) {
            return $this->requestDispatcher->dispatch(
                $request, 
                '*/post/view', 
                ['id' => $postId]
            );
        }

        return false;
    }
}
