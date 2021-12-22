<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\Menu;

class Item extends \FishPig\WordPress\Model\AbstractMetaModel
{
    /**
     * Link types used to determine menu item functionality
     *
     * @const string
     */
    const LINK_TYPE_CUSTOM = 'custom';
    const LINK_TYPE_POST_TYPE = 'post_type';
    const LINK_TYPE_TAXONOMY = 'taxonomy';

    /**
     * @var string
     */
    protected $_eventPrefix = 'wordpress_menu_item';
    protected $_eventObject = 'menu_item';

    /**
     * @var \FishPig\WordPress\Model\ResourceModel\Menu\Item\Collection
     */
    private $children = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \FishPig\WordPress\Model\Context $wpContext,
        \FishPig\WordPress\Api\Data\MetaDataProviderInterface $metaDataProvider,
        \FishPig\WordPress\Model\PostRepository $postRepository,
        \FishPig\WordPress\Model\TermRepository $termRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postRepository = $postRepository;
        $this->termRepository = $termRepository;
        $this->serializer = $wpContext->getSerializer();
        parent::__construct($context, $registry, $wpContext, $metaDataProvider, $resource, $resourceCollection, $data);
    }
    
    /**
     * @return string
     */
    public function getPostType(): string
    {
        return 'nav_menu_item';
    }

    /**
     * @return bool
     */
    public function isCustomLink(): bool
    {
        return $this->getItemType() === self::LINK_TYPE_CUSTOM;
    }

    /**
     * @return bool
     */
    public function isPostTypeLink(): bool
    {
        return $this->getItemType() === self::LINK_TYPE_POST_TYPE;
    }

    /**
     * @return bool
     */
    public function isTaxonomyLink(): bool
    {
        return $this->getItemType() === self::LINK_TYPE_TAXONOMY;
    }

    /**
     * @return false|\FishPig\WordPress\Model\AbstractModel
     */
    public function getObject()
    {
        $this->setObject(false);

        if ($this->isCustomLink() || !$this->getObjectType()) {
            return $this->_getData('object');
        }
        
        try {
            if ($menuObjectId = (int)$this->getMetaValue('_menu_item_object_id')) {
                if ($this->isPostTypeLink()) {
                    $this->setObject(
                        $this->postRepository->getWithType($menuObjectId, [$this->getObjectType()])
                    );
                } elseif ($this->isTaxonomyLink()) {
                    $this->setObject(
                        $this->termRepository->getWithTaxonomy($menuObjectId, [$this->getObjectType()])
                    );
                }
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->setObject(false);
        }

        return $this->_getData('object');
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        return $this->getMetaValue('_menu_item_type');
    }

    /**
     * Retrieve the object type
     *
     * @return string
     */
    public function getObjectType()
    {
        if (!$this->_getData('object_type')) {
            $this->setObjectType($this->getMetaValue('_menu_item_object'));
        }

        return $this->_getData('object_type');
    }

    /**
     * Retrieve the URL for the link
     *
     * @return string
     */
    public function getUrl()
    {
        if ($this->isCustomLink()) {
            return $this->getMetaValue('_menu_item_url');
        } elseif ($this->getObject() !== false) {
            return $this->getObject()->getUrl();
        }
        
        return '';
    }

    /**
     * Retrieve the link label
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->getPostTitle() || $this->isCustomLink()) {
            return $this->getPostTitle();
        } elseif ($this->isPostTypeLink() && $this->getObject()) {
            return $this->getObject()->getPostTitle();
        } elseif ($this->isTaxonomyLink() && $this->getObject()) {
            return $this->getObject()->getName();
        }
        
        return '';
    }

    /**
     * @return bool
     */
    public function isItemActive(): bool
    {
        return false;
    }

    /**
     * @return \FishPig\WordPress\Model\ResourceModel\Menu\Item\Collection
     */
    public function getChildrenItems(): \FishPig\WordPress\Model\ResourceModel\Menu\Item\Collection
    {
        if (null === $this->children) {
            $this->children = $this->getCollection()->addParentItemIdFilter($this->getId());
        }

        return $this->children;
    }

    /**
     * Get menu item title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getData('post_excerpt');
    }

    /**
     * Get menu item description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData('post_content');
    }

    /**
     * Get an array of custom CSS classes
     *
     * @return string
     */
    public function getCssClass()
    {
        if (!$this->hasCssClass()) {
            if ($classString = $this->getMetaValue('_menu_item_classes')) {
                if (!is_array($classString)) {
                    $classString = $this->serializer->unserialize($classString);
                }

                $this->setCssClass(trim(implode(' ', $classString)));
            }
        }

        return $this->getData('css_class');
    }

    /**
     * Get the item target parameter
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->getMetaValue('_menu_item_target');
    }

    /**
     * Get the link relationship
     *
     * @return string
     */
    public function getLinkRelationship()
    {
        return $this->getMetaValue('_menu_item_xfn');
    }
}
