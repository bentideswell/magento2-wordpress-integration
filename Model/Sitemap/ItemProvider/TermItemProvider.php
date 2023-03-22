<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

class TermItemProvider extends AbstractItemProvider
{
    /**
     * @auto
     */
    protected $collectionFactory = null;

    /**
     * @auto
     */
    protected $taxonomyRepository = null;

    /**
     *
     */
    const PRIORITY = 0.5;
    const CHANGE_FREQUENCY = 'weekly';

    /**
     *
     */
    public function __construct(
        \Magento\Sitemap\Model\SitemapItemInterfaceFactory $itemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Logger $logger,
        \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $collectionFactory,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->taxonomyRepository = $taxonomyRepository;
        parent::__construct($itemFactory, $storeManager, $logger);
    }

    /**
     *
     */
    protected function getCollection($storeId): iterable
    {
        return $this->collectionFactory->create()->addTaxonomyFilter(
            ['in' => array_keys($this->taxonomyRepository->getAll())]
        )->load();
    }
}
