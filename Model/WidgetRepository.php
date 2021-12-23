<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class WidgetRepository
{

    /**
     * @var array
     */
    private $widgets = [];

    /**
     * @var \Magento\Framework\View\Layout
     */
    private $layout;

    /**
     * @param  \Magento\Framework\View\Layout $layout
     * @param  array $widgets = []
     * @return void
     */
    public function __construct(
        \Magento\Framework\View\Layout $layout,
        array $widgets = []
    ) {
        $this->layout  = $layout;
        $this->widgets = $widgets;
    }

    /**
     * @param  string $widgetName
     * @return string|false
     */
    public function get(string $widgetName)
    {
        $widgetIndex = preg_match("/([0-9]{1,})$/", $widgetName, $widgetIndexMatch) ? (int)$widgetIndexMatch[1] : 0;
        $widgetName  = rtrim(preg_replace("/-[0-9]+$/i", '', $widgetName), '-');

        if (!isset($this->widgets[$widgetName])) {
            if (!isset($this->widgets['psw'])) {
                return false;
            }

            $this->widgets[$widgetName] = $this->widgets['psw'];
        }

        $widgetBlock = $this->layout->createBlock($this->widgets[$widgetName])
            ->setWidgetType($widgetName)
            ->setWidgetName($widgetName)
            ->setWidgetIndex($widgetIndex);

        if ($widgetBlock instanceof \FishPig\WordPress\Block\Sidebar\Widget\AbstractWidget) {
            return $widgetBlock;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->widgets;
    }
}
