<?php
/*
 *
 */	
namespace FishPig\WordPress\Model;

class WidgetManager
{	
	/*
	 * @var array
	 */
	protected $widgets = [];

	/*
	 *
	 * @param  ModuleManaher $moduleManaher
	 * @return void
	 */
	public function __construct(array $widgets)
	{
		$this->widgets = $widgets;
	}
	
	public function getWidgetClassName($widgetName)
	{
		if (isset($this->widgets[$widgetName])) {
			return $this->widgets[$widgetName];
		}

		echo 'Can not found widget \'' . $widgetName . '\' in widget array.';
		exit;
	}
}
