<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Menu extends \FishPig\WordPress\Model\Term
{
    /**
     * @const string
     */
    const ENTITY = 'wordpress_menu';
    const CACHE_TAG = 'wordpress_menu';

    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_menu';
    protected $_eventObject = 'menu';

    /**
     * @var array
     */
    private $menuCache = null;
    
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \FishPig\WordPress\Model\TaxonomyRepository $taxonomyRepository,
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \FishPig\WordPress\Model\ResourceModel\Menu\Item\CollectionFactory $menuItemCollectionFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->menuItemCollectionFactory = $menuItemCollectionFactory;
        parent::__construct(
            $context,
            $registry,
            $wpContext,
            $metaDataProvider,
            $taxonomyRepository,
            $termRepository,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Gets a simple array of the menu
     * For the actual menu item objects, use getMenuTreeObjects
     *
     * @return array
     */
    public function getMenuTreeArray(): array
    {
        $menu = [];

        if ($tree = $this->getMenuTreeObjects()) {
            foreach ($tree as $node) {
                $menu[] = $this->_getMenuTreeArray($node);
            }
        }
        
        return $menu;
    }

    /**
     *
     */
    private function _getMenuTreeArray($node)
    {
        $data = [
            'id' => 'wp-' . $node->getId(),
            'label' => $node->getLabel(),
            'url' => $node->getUrl(),
            'css_class' => $node->getCssClass(),
            'title' => $node->getTitle(),
            'description' => $node->getDescription(),
            'target' => $node->getTarget(),
        ];

        $children = $node->getChildrenItems();

        if (count($children) > 0) {
            $data['children'] = [];

            foreach ($children as $child) {
                $data['children'][] = $this->_getMenuTreeArray($child);
            }
        }

        return $data;
    }

    /**
     *
     */
    public function getMenuTreeObjects()
    {
        if (null !== $this->menuCache) {
            return $this->menuCache;
        }

        $this->menuCache = false;

        $items = $this->getMenuItems();

        if (count($items) > 0) {
            foreach ($items as $item) {
                $this->menuCache[] = $this->_getMenuTreeObjects($item);
            }
        }

        return $this->menuCache;
    }

    /**
     *
     */
    private function _getMenuTreeObjects($item)
    {
        $children = $item->getChildrenItems();

        if (count($children) > 0) {
            foreach ($children as $child) {
                $this->_getMenuTreeObjects($child);
            }
        }

        return $item;
    }

    /**
     * Retrieve the root menu items
     *
     * @return \FishPig\WordPress\Model\ResourceModel\Menu\Item\Collection
     */
    public function getMenuItems()
    {
        return $this->menuItemCollectionFactory->create()
            ->addParentItemIdFilter()
            ->addMenuFilter($this);
    }

    /**
     * @return string
     */
    public function getTaxonomy(): string
    {
        return 'nav_menu';
    }
}
