<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Vimeo extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'vimeo';
	}
	
	/**
	 * Retrieve the shortcode ID key
	 *
	 * @return string
	 */
	public function getShortcodeIdKey()
	{
		return 'code';
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

				if (!($videoCode = $params->getCode())) {
					continue;
				}

				$width = $params->getW() ? $params->getW() : 625;
				$height = $params->getH() ? $params->getH() : 352;

				$content = str_replace($shortcode->getHtml(), sprintf($this->_getHtmlString(), $videoCode, $width, $height), $content);
			}

			$content = str_replace('<p><iframe', '<iframe', $content);
			$content = str_replace('</iframe></p>', '</iframe>', $content);
		}
	}
	
	/**
	 * Retrieve the HTML pattern for the Vimeo
	 *
	 * @return string
	 */
	protected function _getHtmlString()
	{
		return '<iframe src="http://player.vimeo.com/video/%s" width="%d" height="%d" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';
	}
	
	/**
	 * Retrieve the regex pattern for the inner URL's
	 *
	 * @return string
	 */
	public function getInnerUrlsRegex()
	{
		return '\[%s http:\/\/vimeo.com\/([0-9]{1,})(.*)\]';
	}
	
	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return 'http:\/\/vimeo.com\/([0-9]{1,})';
	}
}
