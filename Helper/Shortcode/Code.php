<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Code extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'code';
	}
	
	/**
	 * Retrieve an array of tag aliases
	 *
	 * @return false|array
	 */
	public function getTagAliases()
	{
		return array('sourcecode');
	}
	
	/**
	 * Apply the Vimeo short code
	 *
	 * @param string &$content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @param array $params = array
	 */	
	protected function _apply(&$content, Fishpig_Wordpress_Model_Post $post)
	{
		if (($shortcodes = $this->_getShortcodes($content)) !== false) {
			foreach($shortcodes as $shortcode) {
				$content = str_replace($shortcode->getOpeningTag(), sprintf('<pre class="brush: %s">', $shortcode->getParams()->getLanguage()), $content);
				$content = str_replace($shortcode->getClosingTag(), '</pre>', $content);
			}
		}
	}
}
