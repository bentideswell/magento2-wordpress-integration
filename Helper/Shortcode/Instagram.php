<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Instagram extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Image sizes
	 *
	 * @const int
	 */
	const SIZE_LARGE = 612;
	const SIZE_MEDIUM = 306;
	const SIZE_THUMBNAIL = 150;
	
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'instagram';
	}
	
	/**
	 * Apply the Vimeo short code
	 *
	 * @param string &$content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return void
	 */	
	protected function _apply(&$content,  Fishpig_Wordpress_Model_Post $post)
	{
		if (($shortcodes = $this->_getShortcodes($content)) !== false) {
			foreach($shortcodes as $shortcode) {
				$url = $shortcode->getParams()->getUrl();
				$width = (int)trim($shortcode->getParams()->getWidth());
				
				if ($width > self::SIZE_MEDIUM) {
					$width = self::SIZE_LARGE;
					$size = 'l';
				}
				else if ($width > self::SIZE_THUMBNAIL) {
					$width = self::SIZE_MEDIUM;
					$size = 'm';
				}
				else {
					$width = self::SIZE_THUMBNAIL;
					$size = 't';
				}

				$content = str_replace($shortcode->getHtml(), sprintf($this->_getHtmlString(), $url, $width, $url, $size), $content);
			}
		}
	}
	
	/**
	 * Retrieve the HTML pattern for the Vimeo
	 *
	 * @return string
	 */
	protected function _getHtmlString()
	{
		return '<a href="%s"><img width="%s" alt="" src="%smedia/?size=%s"></a>';
	}
	
	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return '\n(http:\/\/instagram.com\/p\/[a-zA-Z0-9_-]{1,}[\/]{1,})';
	}
}
