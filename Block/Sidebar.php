<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block;

class Sidebar extends \Magento\Framework\View\Element\Template
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \FishPig\WordPress\Model\WidgetRepository $widgetRepository,
        \FishPig\WordPress\Model\OptionRepository $optionRepository,
        \FishPig\WordPress\Model\PluginManager $pluginManager,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        array $data = []
    ) {
        $this->widgetRepository = $widgetRepository;
        $this->optionRepository = $optionRepository;
        $this->pluginManager = $pluginManager;
        $this->registry = $registry;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Load all enabled widgets
     *
     * @return \FishPig\WordPress\Block\Sidebar
     */
    protected function _beforeToHtml()
    {
        if ($widgets = $this->getWidgetsArray()) {
            foreach ($widgets as $widgetType) {
                if ($block = $this->widgetRepository->get($widgetType)) {
                    $this->setChild('wordpress_widget_' . $widgetType, $block);
                }
            }
        }

        if (!$this->getTemplate()) {
            $this->setTemplate('FishPig_WordPress::sidebar.phtml');
        }

        return parent::_beforeToHtml();
    }

    /**
     * Get the widget area
     * Set a custom widget area by calling $this->setWidgetArea('your-custom-area')
     *
     * @return string
     */
    public function getWidgetArea()
    {
        if (!$this->hasWidgetArea()) {
            $this->setData('widget_area', 'sidebar-main');
        }

        return $this->_getData('widget_area');
    }

    /**
     * Set the widget area
     *
     * @param  string $widgetArea
     * @return $this
     */
    public function setWidgetArea($widgetArea)
    {
        return $this->setData('widget_area', $widgetArea);
    }

    /**
     * Retrieve the sidebar widgets as an array
     *
     * @return false|array
     */
    public function getWidgetsArray()
    {
        if ($this->getWidgetArea()) {
            $widgets = $this->optionRepository->get('sidebars_widgets');

            if ($widgets) {
                $widgets = $this->serializer->unserialize($widgets);
                $realWidgetArea = $this->getRealWidgetArea();

                if (isset($widgets[$realWidgetArea])) {
                    return $widgets[$realWidgetArea];
                }
            }
        }

        return false;
    }
    
    /**
     * Get the real widget area by using the Custom Sidebars plugin
     *
     * @return string
     */
    public function getRealWidgetArea()
    {
        if (!$this->pluginManager->isEnabled('custom-sidebars/customsidebars.php')) {
            return $this->getWidgetArea();
        }

        if (!($settings = $this->getCustomSidebarsModifiable())) {
            return $this->getWidgetArea();
        }

        $handles = $this->getLayout()->getUpdate()->getHandles();

        if (!isset($settings['modifiable'])
            || array_search($this->getWidgetArea(), $settings['modifiable']) === false) {
            return $this->getWidgetArea();
        }

        if ($post = $this->registry->registry('wordpress_post')) {
            if ($value = $post->getMetaValue('_cs_replacements')) {
                $value = $this->serializer->unserialize($value);

                if (isset($value[$this->getWidgetArea()])) {
                    return $value[$this->getWidgetArea()];
                }
            }

            // Single post by type
            if ($widgetArea = $this->_getArrayValue(
                $settings,
                'post_type_single/' . $post->getPostType() . '/' . $this->getWidgetArea()
            )) {
                return $widgetArea;
            }

            // Single post by category
            /*
            if ($categoryIdResults = $post->getResource()->getParentTermsByPostId(
                $post->getId(),
                'category'
            )) {
                $categoryIdResults = array_pop($categoryIdResults);

                if (isset($categoryIdResults['category_ids'])) {
                    foreach (explode(',', $categoryIdResults['category_ids']) as $categoryId) {
                        if ($widgetArea = $this->_getArrayValue(
                            $settings,
                            'category_single/' . $categoryId . '/' . $this->getWidgetArea()
                        )) {
                            return $widgetArea;
                        }
                    }
                }
            }*/
        }
        
        if ($postType = $this->registry->registry('wordpress_post_type')) {
            if (isset($settings['post_type_archive'][$postType->getPostType()][$this->getWidgetArea()])) {
                return $settings['post_type_archive'][$postType->getPostType()][$this->getWidgetArea()];
            }
        }
        
        if ($term = $this->registry->registry('wordpress_term')) {
            if ($widgetArea = $this->_getArrayValue(
                $settings,
                $term->getTaxonomy() . '_archive/' . $term->getId() . '/' . $this->getWidgetArea()
            )) {
                return $widgetArea;
            }
        }
        
        if (in_array('wordpress_homepage', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'blog/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        }
        
        if ($author = $this->registry->registry('wordpress_author')) {
            if ($widgetArea = $this->_getArrayValue(
                $settings,
                'authors/' . $author->getId() . '/' . $this->getWidgetArea()
            )) {
                return $widgetArea;
            }
        }

        if (in_array('wordpress_search_index', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'search/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        }
        
        if (in_array('wordpress_archive_view', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'date/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        }
        
        if (in_array('wordpress_post_tag_view', $handles)) {
            if ($widgetArea = $this->_getArrayValue($settings, 'tags/' . $this->getWidgetArea())) {
                return $widgetArea;
            }
        }

        return $this->getWidgetArea();
    }

    /**
     * Retrieve a deep value from a multideimensional array
     *
     * @param  array  $arr
     * @param  string $key
     * @return string|null
     */
    protected function _getArrayValue($arr, $key)
    {
        $keys = explode('/', trim($key, '/'));

        foreach ($keys as $key) {
            if (!isset($arr[$key])) {
                return null;
            }

            $arr = $arr[$key];
        }

        return $arr;
    }

    /**
     * Determine whether or not to display the sidebar
     *
     * @return int
     */
    public function canDisplay()
    {
        return 1;
    }

    /**
     * @return ?array
     */
    private function getCustomSidebarsModifiable(): ?array
    {
        if ($csModifiableOption = $this->optionRepository->get('cs_modifiable')) {
            return $this->serializer->unserialize($csModifiableOption) ?? null;
        }
        
        return null;
    }
}
