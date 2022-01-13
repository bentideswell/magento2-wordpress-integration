<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

use FishPig\WordPress\App\Debug\TestPool;

class TaxonomyTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $termCollectionFactory,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->taxonomyRepository = $taxonomyRepository;
        $this->termRepository = $termRepository;
        $this->termCollectionFactory = $termCollectionFactory;
        $this->layout = $layout;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        foreach ($this->taxonomyRepository->getAll() as $taxonomy) {
            $taxonomy->getAllRoutes();
            $terms = $this->termCollectionFactory->create()
                ->addTaxonomyFilter($taxonomy->getTaxonomy())
                ->setPageSize($options[TestPool::ENTITY_LIMIT] ?: 0)
                ->load();

            foreach ($terms as $term) {
                $term = $this->termRepository->get($term->getId());
                $term->getName();
                $term->getUrl();
                $term->getPostCollection();
                $term->getContent();
                $term->getTaxonomyInstance();
                $term->getParentTerm();
                $term->getChildrenTerms();
                $term->getParentId();
                $term->getPostCount();
                $term->getChildIds();
                $term->getResource();
                $term->getCollection();

                if (isset($options[TestPool::RUN_BLOCK_TESTS]) && $options[TestPool::RUN_BLOCK_TESTS] === true) {
                    $this->layout->createBlock(\FishPig\WordPress\Block\Term\View::class)->setTerm($term)->toHtml();
                }
            }
        }
    }
}
