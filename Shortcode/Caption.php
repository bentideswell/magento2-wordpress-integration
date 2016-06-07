<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Shortcode;

class Caption extends AbstractShortcode
{
	/**
	 *
	 *
	 * @return 
	**/
	public function getTag()
	{
		return 'caption';
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	protected function _process()
	{
		if (($shortcodes = $this->_getShortcodesByTag($this->getTag())) !== false) {
			foreach($shortcodes as $it => $shortcode) {
				$params = $shortcode->getParams();
				$caption = $params->getCaption() ? sprintf('<p class="wp-caption-text">%s</p>', trim($params->getCaption())) : '';
				$style = '';
				
				if ($params->getWidth()) {
					$style = $params->getAlign() !== 'center' ? ' style="width:'.($params->getWidth()+10).'px;"' : '';
				}

				$html = implode('', array(
					sprintf('<div id="%s" class="wp-caption %s"%s>', $params->getId(), $params->getAlign(), $style),
					$shortcode->getInnerContent(),
					$caption,
					'</div>'
				));

				$this->setValue(str_replace($shortcode['html'], $html, $this->getValue()));
			}
		}
		
		return $this;
	}
}
