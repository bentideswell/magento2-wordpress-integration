<?php
/*
 *
 */
namespace FishPig\WordPress\Model;

/* Constructor Args */
use FishPig\WordPress\Helper\Autop;

class ShortcodeManager
{
	/*
	 * @var array
	 */
	protected $shortcodes = [];
	
	/*
	 * @var Autop
	 */
	protected $autop;

	/*
	 *
	 *
	 *
	 */
	public function __construct(Autop $autop, array $shortcodes = [])	
	{
		$this->autop = $autop;
		
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
		if ($args && is_object($args)) {
			$args = ['object' => $args];
		}

		if ($shortcodes = $this->getShortcodes()) {
			foreach($shortcodes as $shortcode) {
				// Legacy support. Old shortcodes returned false when not required
				if (($returnValue = $shortcode->renderShortcode($input, $args)) !== false) {
					$input = $returnValue;
				}
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
	
	/*
	 *
	 *
	 * @param  string $string
	 * @return string
	 */
	public function addParagraphTagsToString($string)
	{
		return $this->autop->addParagraphTagsToString($string);
	}
}
