<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Dailymotion extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'dailymotion';
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
				if ($id = $shortcode->getParams()->getId()) {
					$content = str_replace($shortcode->getHtml(), sprintf($this->_getHtmlString(), $id), $content);
				}
			}
		}
	}
	
	/**
	 * Retrieve the shortcode middle content
	 *
	 * @return string
	 */
	public function getConvertedUrlsMiddle()
	{
		return ' id=';
	}
	
	/**
	 * Retrieve the HTML pattern for the Vimeo
	 *
	 * @return string
	 */
	protected function _getHtmlString()
	{
		return '<iframe src="http://www.dailymotion.com/embed/video/%s" width="625" height="468" frameborder="0"></iframe>';
	}
	
	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return 'http:\/\/www.dailymotion.com\/video\/([^_]{1,})[^\s]{1,}';
	}
}

