<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Sidebar\Widget;

abstract class AbstractWidget extends \FishPig\WordPress\Block\AbstractBlock
{
    /**
     * Retrieve the default title
     *
     * @return string
     */
    public function getTitle()
    {
        if (($title = $this->_getData('title')) !== false) {
            return $title ? $title : $this->getDefaultTitle();
        }

        return false;
    }

    /**
     *
     */
    public function getDefaultTitle()
    {
        return '';
    }

    /**
     * Attempt to load the widget information from the WordPress options table
     *
     * @return FishPig\WordPress\Block_Sidebar_Widget_Abstract
     */
    protected function _beforeToHtml()
    {
        if ($this->getWidgetType()) {
            $data = $this->optionRepository->get('widget_' . $this->getWidgetType());

            if ($data) {
                $data = unserialize($data);

                if (isset($data[$this->getWidgetIndex()])) {
                    foreach ($data[$this->getWidgetIndex()] as $field => $value) {
                        $this->setData($field, $value);
                    }
                }
            }
        }

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve an ID to be used for the list
     *
     * @return string
     */
    public function getListId()
    {
        if (!$this->hasListId()) {
            $hash = 'wp-' . md5(rand(1111, 9999) . $this->getTitle() . $this->getWidgetType());

            $this->setListId(substr($hash, 0, 6));
        }

        return $this->_getData('list_id');
    }

    /**
     *
     *
     * @return int
     */
    public function getWidgetId()
    {
        return (int)$this->getWidgetIndex();
    }
}
