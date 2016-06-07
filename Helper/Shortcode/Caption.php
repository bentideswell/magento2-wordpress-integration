<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Caption extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'caption';
	}
	
	/**
	 * Apply the Vimeo short code
	 *
	 * @param string &$content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return void
	 */	
	protected function _apply(&$content, Fishpig_Wordpress_Model_Post $post)
	{
		if (($shortcodes = $this->_getShortcodes($content)) !== false) {
			foreach($shortcodes as $shortcode) {
				$params = $shortcode->getParams();
				$caption = $params->getCaption() ? sprintf('<p class="wp-caption-text">%s</p>', trim($params->getCaption())) : '';
				$style = '';
				
				if ($params->getWidth()) {
					$style = $params->getAlign() != 'center' ? ' style="width:'.($params->getWidth()+10).'px;"' : '';
				}

				$html = array(
					sprintf('<div id="%s" class="wp-caption %s"%s>', $params->getId(), $params->getAlign(), $style),
					$shortcode->getInnerContent(),
					$caption,
					'</div>'
				);

				$content = str_replace($shortcode->getHtml(), implode('', $html), $content);
			}
		}
	}
}
