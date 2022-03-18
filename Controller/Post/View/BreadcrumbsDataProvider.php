<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Post\View;

class BreadcrumbsDataProvider implements \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface
{
    /**
     * @param \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository
     */
    public function __construct(
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\Helper\FrontPage $frontPageHelper,
        \FishPig\WordPress\App\Logger $logger
    ) {
        $this->taxonomyRepository = $taxonomyRepository;
        $this->frontPageHelper = $frontPageHelper;
        $this->logger = $logger;
    }

    /**
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return array
     */
    public function getData(\FishPig\WordPress\Api\Data\ViewableModelInterface $post): array
    {
        $crumbs = [];

        if ($post->isFrontPage()) {
            return $crumbs;
        }

        $postType = $post->getTypeInstance();
        $slugParts  = explode('/', trim($postType->getSlug(), '/'));

        if (!$postType->isDefault() && $postType->hasArchive()) {
            $crumbs[$postType::ENTITY] = [
                'label' => __($postType->getName()),
                'link' => $postType->getUrl()
            ];
        }

        foreach ($slugParts as $slugPart) {
            if ($this->isPostTypeBaseSlug($slugPart, $postType)) {
                if (!$postType->isDefault() && $postType->hasArchive()) {
                    $crumbs[$postType::ENTITY] = [
                        'label' => $postType->getName(),
                        'link' => $postType->getUrl(),
                    ];
                }
            } elseif (substr($slugPart, 0, 1) === '%' && substr($slugPart, -1) === '%') {
                try {
                    if ($taxonomy = $this->taxonomyRepository->get(substr($slugPart, 1, -1))) {
                        if ($term = $post->getParentTerm($taxonomy->getTaxonomy())) {
                            $crumbs[$term::ENTITY . '_' . $term->getId()] = [
                                'label' => $term->getName(),
                                'link' => $term->getUrl(),
                            ];
                        }
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) { // phpcs:ignore -- empty catch
                }
            } elseif ($postType->isHierarchical() && strlen($slugPart) > 1 && substr($slugPart, 0, 1) !== '.') {
                $this->logger->debug('Post breadcrumbs data provider error on line 68. Slug = ' . $postType->getSlug());
                continue;
                
                /*
                $parent = $this->factory->create('Post')->setPostType('page')->load($slugPart, 'post_name');

                if ($parent->getId()) {
                    $objects['parent_post_' . $parent->getId()] = $parent;
                }*/
            } elseif ($postType->isDefault() && ($postsPage = $this->frontPageHelper->getPostsPage())
                      && $slugPart === $postsPage->getPostName()) {
                $crumbs[$postType::ENTITY] = [
                    'label' => $postsPage->getName(),
                    'link' => $postsPage->getUrl(),
                ];
            }
        }

        if ($postType->isHierarchical()) {
            $parent = $post;

            while (($parent = $parent->getParentPost()) !== false) {
                $crumbs['parent_post_' . $parent->getId()] = [
                    'label' => $parent->getName(),
                    'link' => $parent->getUrl()
                ];
            }

            $crumbs = array_reverse($crumbs);
        }

        $crumbs['post'] = [
            'label' => __($post->getName()),
        ];

        return $crumbs;
    }
    
    /**
     * @param  string $slugPart
     * @param  \FishPig\WordPress\Model\PostType $postType
     * @return bool
     */
    private function isPostTypeBaseSlug(string $slugPart, \FishPig\WordPress\Model\PostType $postType): bool
    {
        if ($slugPart === $postType->getPostType()) {
            return true;
        }
        
        if ($postType->getArchiveSlug()) {
            return trim($postType->getArchiveSlug(), '/') === $slugPart;
        }
        
        return false;
    }
}
