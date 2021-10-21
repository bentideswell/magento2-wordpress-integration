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
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository
    ) {
        $this->taxonomyRepository = $taxonomyRepository;
    }

    /**
     * @param  \FishPig\WordPress\Api\Data\Entity\ViewableInterface $object
     * @return array
     */
    public function getData(\FishPig\WordPress\Api\Data\Entity\ViewableInterface $post): array 
    {
        $crumbs = [];

        if ($post->isFrontPage()) {
            return $crumbs;
        }

        $postType = $post->getTypeInstance();
        $slugParts  = explode('/', trim($postType->getSlug(), '/'));

        foreach ($slugParts as $slugPart) {
            if ($this->isPostTypeBaseSlug($slugPart, $postType)) {
                if (!$postType->isDefault() && $postType->hasArchive()) {
                    $crumbs[$postType->getPostType()] = [
                        'label' => $postType->getName(),
                        'link' => $postType->getUrl(),
                    ];
                }
            } elseif (substr($slugPart, 0, 1) === '%' && substr($slugPart, -1) === '%') {
                try {
                    if ($taxonomy = $this->taxonomyRepository->get(substr($slugPart, 1, -1))) {
                        echo __METHOD__;exit;
                        if ($term = $post->getParentTerm($taxonomy->getTaxonomyType())) {
                            $crumbs[$term::ENTITY . '_' . $term->getId()] = [
                                'label' => $term->getName(),
                                'link' => $term->getUrl(),
                            ];
                        }
                    }
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    /**/
                }
            } elseif (strlen($slugPart) > 1 && substr($slugPart, 0, 1) !== '.') {
                    echo __METHOD__;exit;
                $parent = $this->factory->create('Post')->setPostType('page')->load($slugPart, 'post_name');

                if ($parent->getId()) {
                    $objects['parent_post_' . $parent->getId()] = $parent;
                }
            }
        }

        if ($postType->isHierarchical()) {
            $parent = $post;
            $buffer = [];

            while (($parent = $parent->getParentPost()) !== false) {
                $buffer['parent_post_' . $parent->getId()] = $parent;
            }

            $objects = $objects + array_reverse($buffer);
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
