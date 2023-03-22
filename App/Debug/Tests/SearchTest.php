<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Tests;

class SearchTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     * @auto
     */
    protected $searchFactory = null;

    /**
     * @auto
     */
    protected $postCollectionFactory = null;

    /**
     *
     */
    private $post = false;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\SearchFactory $searchFactory,
        \FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
    ) {
        $this->searchFactory = $searchFactory;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $post = $this->getPost();

        $searchTerms = array_filter([
            $post ? $post->getName() : null, // This one should exist
            'TH1SDo£SN0T3Xist'               // This one won't exist
        ]);

        foreach ($searchTerms as $searchTerm) {
            $search = $this->searchFactory->create([
                'data' => ['search_term' => $searchTerm]
            ]);
            $search->getName();
            $search->getUrl();
            $search->getSearchTerm();
            $search->getPostTypes();
            $search->getPostCollection()->load();
        }
    }

    /**
     *
     */
    private function getPost(): ?\FishPig\WordPress\Model\Post
    {
        if ($this->post === false) {
            $posts = $this->postCollectionFactory->create()
                ->setPageSize(1)
                ->addIsViewableFilter()
                ->addPostTypeFilter('post')
                ->load();

            $this->post = count($posts) > 0 ? $posts->getFirstItem() : null;
        }

        return $this->post;
    }
}
