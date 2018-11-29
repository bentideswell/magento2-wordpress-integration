<?php
/*
 *
 */
namespace FishPig\WordPress\Block;

/* Parent Class */
use FishPig\WordPress\Block\AbstractBlock;

class Shortcode extends AbstractBlock
{
	/*
	 * Render html output
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		if (!$this->_beforeToHtml()) {
		  return '';
		}

		if (!($shortcode = $this->getShortcode())) {
			return '';
		}

		return $this->shortcodeManager->renderShortcode($shortcode);
	}
	
	/*
	 *
	 *
	 * @return string
	 */
	public function getShortcode()
	{
		return str_replace("\\\"", '"', $this->getData('shortcode'));
	}
}
