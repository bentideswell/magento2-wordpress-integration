<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Term\View;

class BreadcrumbsDataProvider implements \FishPig\WordPress\Api\Controller\Action\BreadcrumbsDataProviderInterface
{
    /**
     * @auto
     */
    protected $postTypeRepository = null;

    /**
     * @param \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository
     */
    public function __construct(
        \FishPig\WordPress\Model\PostTypeRepository $postTypeRepository
    ) {
        $this->postTypeRepository = $postTypeRepository;
    }

    /**
     * @param  \FishPig\WordPress\Api\Data\ViewableModelInterface $object
     * @return array
     */
    public function getData(\FishPig\WordPress\Api\Data\ViewableModelInterface $term): array
    {
        $crumbs = [];
        $taxonomy = $term->getTaxonomyInstance();
        $postTypes = $this->postTypeRepository->getAll();
        ;

        if (count($postTypes) > 2) {
            foreach ($postTypes as $postType) {
                if ($postType->hasArchive() && $postType->getArchiveSlug() === $taxonomy->getSlug()) {
                    $crumbs[$postType::ENTITY . '_archive_' . $postType->getPostType()] = [
                        'label' => __($postType->getName()),
                        'link' => $postType->getUrl(),
                    ];

                    break;
                }
            }
        }

        if ($taxonomy->isHierarchical()) {
            // Get all parent terms to build crumbs with. These will be in the
            // wrong order so reverse them before buidling.
            $buffer = $term;
            $ancestorTerms = [];

            while ($buffer->getParentTerm()) {
                $buffer = $buffer->getParentTerm();
                $ancestorTerms[] = $buffer;
            }

            if ($ancestorTerms = array_reverse($ancestorTerms)) {
                foreach ($ancestorTerms as $buffer) {
                    $crumbs['term_' . $buffer->getId()] = [
                        'label' => (string)__($buffer->getName()),
                        'title' => (string)__($buffer->getName()),
                        'link' => $buffer->getUrl(),
                    ];
                }
            }
        }

        $crumbs[$term::ENTITY] = [
            'label' => (string)__($term->getName())
        ];

        return $crumbs;
    }
}
