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
     * @auto
     */
    protected $requestDispatcher = null;

    /**
     * @auto
     */
    protected $routerUrlHelper = null;

    /**
     * @auto
     */
    protected $permalinkResource = null;

    /**
     * @auto
     */
    protected $frontPage = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Model\ResourceModel\Post\Permalink $permalinkResource,
        \FishPig\WordPress\Helper\FrontPage $frontPage
    ) {
        $this->requestDispatcher = $requestDispatcher;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->permalinkResource = $permalinkResource;
        $this->frontPage = $frontPage;
    }

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $postId = $this->getPostIdByPathInfo(
            $this->routerUrlHelper->getRelativePathInfo($request)
        );

        if (!$postId) {
            return false;
        }

        if ($this->isPostIdPostsPageId($postId)) {
            return $this->requestDispatcher->dispatch(
                $request,
                '*/postType/view',
                ['post_type' => 'post']
            );
        }

        return $this->requestDispatcher->dispatch(
            $request,
            '*/post/view',
            ['id' => $postId]
        );
    }

    /**
     *
     */
    public function getPostIdByPathInfo(string $pathInfo): ?int
    {
        return (int)$this->permalinkResource->getPostIdByPathInfo($pathInfo) ?: null;
    }

    /**
     *
     */
    public function isPostIdPostsPageId(int $postId): bool
    {
        return $this->frontPage->getPostsPageId() === $postId;
    }
}
