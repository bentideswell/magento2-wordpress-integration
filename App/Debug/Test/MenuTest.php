<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class MenuTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\MenuFactory $menuFactory,
        \FishPig\WordPress\Model\ResourceModel\Term\CollectionFactory $termCollectionFactory
    ) {
        $this->menuFactory = $menuFactory;
        $this->termCollectionFactory = $termCollectionFactory;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $menus = $this->termCollectionFactory->create()
            ->addTaxonomyFilter('nav_menu')
            ->setPageSize(1)
            ->load();
        
        if (count($menus) === 0) {
            return;
        }

        $this->menuFactory->create()
            ->load($menus->getFirstItem()->getId())
            ->getMenuTreeArray();
    }
}
