<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

class WidgetManager extends \FishPig\WordPress\Model\WidgetRepository
{
    /**
     * @param  string @widgetName
     * @return string|false
     */
    public function getWidget($widgetName)
    {
        return $this->get($widgetName);
    }
}
