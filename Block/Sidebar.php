<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Block;

class Sidebar extends AbstractBlock
{	
	/**
	 * Stores all templates for each widget block
	 *
	 * @var array
	 */
	protected $_widgets = array();

	/**
	 * Add a widget type
	 *
	 * @param string $name
	 * @param string $block
	 * @return \FishPig\WordPress\Block\Sidebar
	 */
	public function addWidgetType($name, $class)
	{
		if (!isset($this->_widgets[$name])) {
			$this->_widgets[$name] = $class;
		}
	
		return $this;
	}
	
	/**
	 * Retrieve information about a widget type
	 *
	 * @param string $name
	 * @return false|array
	 */
	public function getWidgetType($name)
	{
		return isset($this->_widgets[$name]) ? $this->_widgets[$name] : false;
	}
	
	/**
	 * Load all enabled widgets
	 *
	 * @return \FishPig\WordPress\Block\Sidebar
	 */
	protected function _beforeToHtml()
	{
		if ($widgets = $this->getWidgetsArray()) {
			$this->_initAvailableWidgets();

			foreach($widgets as $widgetType) {
				$name = $this->_getWidgetName($widgetType);
				$widgetIndex = $this->_getWidgetIndex($widgetType);

				if ($class = $this->getWidgetType($name)) {
					if ($block = $this->getLayout()->createBlock($class)) {
						$block->setWidgetType($name);
						$block->setWidgetIndex($widgetIndex);
						
						$this->setChild('wordpress_widget_' . $widgetType, $block);
					}
				}
			}
		}
		
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar.phtml');
		}

		return parent::_beforeToHtml();
	}
	
	/**
	 * Retrieve the widget name
	 * Strip the trailing number and hyphen
	 *
	 * @param string $widget
	 * @return string
	 */
	protected function _getWidgetName($widget)
	{
		return rtrim(preg_replace("/[^a-z_-]/i", '', $widget), '-');
	}
	
	/**
	 * Retrieve the widget name
	 * Strip the trailing number and hyphen
	 *
	 * @param string $widget
	 * @return string
	 */
	protected function _getWidgetIndex($widget)
	{
		if (preg_match("/([0-9]{1,})/",$widget, $results)) {
			return $results[1];
		}
		
		return false;
	}
	
	/*
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
	 * Retrieve the sidebar widgets as an array
	 *
	 * @return false|array
	 */
	public function getWidgetsArray()
	{
		if ($this->getWidgetArea()) {
			$widgets = $this->_config->getOption('sidebars_widgets');

			if ($widgets) {
				$widgets = unserialize($widgets);

				$realWidgetArea = $this->getWidgetArea();

				if (isset($widgets[$realWidgetArea])) {
					return $widgets[$realWidgetArea];
				}
			}
		}

		return false;
	}
	
	/**
	 * Retrieve a deep value from a multideimensional array
	 *
	 * @param array $arr
	 * @param string $key
	 * @return string|null
	 */
	protected function _getArrayValue($arr, $key)
	{
		$keys = explode('/', trim($key, '/'));
		
		foreach($keys as $key) {
			if (!isset($arr[$key])) {
				return null;
			}
			
			$arr = $arr[$key];
		}
		
		return $arr;
	}
	
	/**
	 * Initialize the widgets from the config.xml
	 *
	 * @return $this
	 */
	protected function _initAvailableWidgets()
	{
		$availableWidgets = $this->_config->getWidgets();
		
		foreach($availableWidgets as $name => $class) {
			$this->addWidgetType($name, $class);
		}
		
		return $this;
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
}
