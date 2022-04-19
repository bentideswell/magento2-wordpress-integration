<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Router;

class PostWithCategoryFallbackRouter implements \Magento\Framework\App\RouterInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Controller\Router\RequestDispatcher $requestDispatcher,
        \FishPig\WordPress\Controller\Router\UrlHelper $routerUrlHelper,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository
    ) {
        $this->requestDispatcher = $requestDispatcher;
        $this->routerUrlHelper = $routerUrlHelper;
        $this->postRepository = $postRepository;
        $this->postTypeRepository = $postTypeRepository;
        $this->taxonomyRepository = $taxonomyRepository;
    }

    /**
     * 
     * @param  \Magento\Framework\App\RequestInterface $request
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $relativePathInfo = $this->routerUrlHelper->getRelativePathInfo($request);

        if (strpos($relativePathInfo, '/') === false) {
            return false;
        }

        $permalinkStructure = trim($this->postTypeRepository->get('post')->getPermalinkStructure(), '/');

        if (strpos($permalinkStructure, '%category%') === false) {
            return false;
        }

        if ($permalinkStructure === '%category%/%postname%') {
            $postName = substr($relativePathInfo, strrpos($relativePathInfo, '/')+1);
        } elseif ($permalinkStructure === '%category%/%postname%.html') {
            $postName = str_replace('.html', '', substr($relativePathInfo, strrpos($relativePathInfo, '/')+1));
        } else {
            return false;
        }

        try {
            $post = $this->postRepository->getByField($postName, 'post_name');
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }

        $category = $this->taxonomyRepository->get('category');

        foreach ($category->getAllRoutes() as $categoryId => $categoryRoute) {
            if (strpos('/' . $relativePathInfo . '/', $categoryRoute) === false) {
                continue;
            }

            $postAlternatePathInfo = str_replace(
                ['%category%', '%postname%'],
                [$categoryRoute, $post->getData('post_name')],
                $permalinkStructure
            );

            if ($relativePathInfo === $postAlternatePathInfo) {
                // Check that the category is associated with $post
                $terms = $post->getTermCollection(
                    $category->getTaxonomy()
                )->addFieldToFilter(
                    'main_table.term_id',
                    $categoryId
                )->setPageSize(
                    1
                )->load();

                if (count($terms) > 0) {
                    return $this->requestDispatcher->redirect($request, $post->getUrl(), 301);
                }
            }
        }

        return false;
    }
}
