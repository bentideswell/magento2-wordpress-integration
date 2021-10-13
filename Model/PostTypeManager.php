<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\PostType;
use FishPig\WordPress\Model\PostTypeFactory;
use FishPig\WordPress\Legacy\Model\OptionManager;

class PostTypeManager
{
    /**
     * @var
     */
    protected $moduleManager;

    /**
     * @var
     */
    protected $storeManager;

    /**
     *
     */
    protected $optionManager;

    /**
     * @var array
     */
    protected $types = [];

    /**
     *
     *
     *
     */
    public function __construct(
        ModuleManager $moduleManager,
        StoreManagerInterface $storeManager,
        PostTypeFactory $postTypeFactory,
        OptionManager $optionManager,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->moduleManager   = $moduleManager;
        $this->storeManager    = $storeManager;
        $this->postTypeFactory = $postTypeFactory;
        $this->optionManager   = $optionManager;
        $this->eventManager = $eventManager;

        $this->load();
    }

    /**
     *
     *
     * @return $this
     */
    public function load()
    {
        $storeId = $this->getStoreId();

        if (isset($this->types[$storeId])) {
            return $this;
        }

        if ($postTypeData = $this->getPostTypeDataFromAddon()) {
            foreach ($postTypeData as $postType) {
                $this->registerPostType(
                    $this->postTypeFactory->create()->addData($postType)
                );
            }
        } else {
            $this->registerPostType(
                $this->postTypeFactory->create()->addData(
                    [
                        'post_type' => 'post',
                        'rewrite' => ['slug' => $this->optionManager->getOption('permalink_structure')],
                        'taxonomies' => ['category', 'post_tag'],
                        '_builtin' => true,
                        'public' => true,
                    ]
                )
            );

            $this->registerPostType(
                $this->postTypeFactory->create()->addData(
                    [
                        'post_type' => 'page',
                        'rewrite' => ['slug' => '%postname%/'],
                        'hierarchical'  => true,
                        'taxonomies' => [],
                        '_builtin' => true,
                        'public' => true,
                    ]
                )
            );

            $transport = new \Magento\Framework\DataObject();

            $this->eventManager->dispatch('wordpress_get_post_types', ['transport' => $transport]);
            
            if ($postTypes = $transport->getPostTypes()) {
                foreach ($postTypes as $postTypeData) {
                    if (is_array($postTypeData)) {
                        $this->registerPostType(
                            $this->postTypeFactory->create()->addData($postTypeData)
                        );
                    } elseif ($postTypeData instanceof PostType) {
                        $this->registerPostType($postTypeData);
                    }
                }
            }
        }

        return $this;
    }

    public function registerPostType(PostType $postType)
    {
        $storeId = $this->getStoreId();

        if (!isset($this->types[$storeId])) {
            $this->types[$storeId] = [];
        }

        $this->types[$storeId][$postType->getPostType()] = $postType;

        return $this;
    }

    /**
     *
     *
     * @return false|Type
     */
    public function getPostType($type = null)
    {
        if ($types = $this->getPostTypes()) {
            if ($type === null) {
                return $types;
            } elseif (isset($types[$type])) {
                return $types[$type];
            }
        }

        return false;
    }

    /**
     *
     *
     * @return false|array
     */
    public function getPostTypes()
    {
        $storeId = $this->getStoreId();

        $this->load();

        return isset($this->types[$storeId]) ? $this->types[$storeId] : false;
    }

    /**
     *
     *
     * @return bool
     */
    protected function isAddonEnabled()
    {
        return $this->moduleManager->isOutputEnabled('FishPig_WordPress_PostTypeTaxonomy');
    }

    /**
     *
     *
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
    }

    /**
     *
     *
     * @return bool
     */
    public function getPostTypeDataFromAddon()
    {
        return false;
    }
}
