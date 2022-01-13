<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class SearchTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\SearchFactory $searchFactory
    ) {
        $this->searchFactory = $searchFactory;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $search = $this->searchFactory->create(['data' => ['search_term' => 'King']]);
        $search->getName();
        $search->getUrl();
        $search->getSearchTerm();
        $search->getPostTypes();
    }
}
