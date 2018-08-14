<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

class ShortcodeManager
{
	/*
	 * @var array
	 */
	protected $shortcodes = [];

	/*
	 *
	 *
	 *
	 */
	public function __construct(array $shortcodes = [])	
	{
		foreach($shortcodes as $alias => $shortcode) {
			if (!method_exists($shortcode, 'isEnabled') || $shortcode->isEnabled()) {
				$this->shortcodes[$alias] = $shortcode;
			}
		}
	}

	/*
	 *
	 *
	 *
	 */
	public function renderShortcode($input, $args = [])
	{	
		if ($shortcodes = $this->getShortcodes()) {
			foreach($shortcodes as $shortcode) {
				$input = $shortcode->renderShortcode($input, $args);
			}
		}

		return $input;
	}
	
	/*
	 *
	 *
	 *
	 */
	public function getShortcodes()
	{
		return $this->shortcodes;
	}

	/*
	 *
	 *
	 *
	 */
	public function getShortcodesThatRequireAssets()
	{
		$buffer = [];
		
		foreach($this->shortcodes as $alias => $shortcode) {
			if (method_exists($shortcode, 'requiresAssetInjection') && $shortcode->requiresAssetInjection()) {
				$buffer[$alias] = $shortcode;
			}
		}
		
		return $buffer;
	}
}
