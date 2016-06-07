<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Scribd extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'scribd';
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
					$content = str_replace($shortcode->getHtml(), sprintf($this->_getHtmlString(), $id, $id), $content);
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
		return ' mode=list id=';
	}
	
	/**
	 * Retrieve the HTML pattern for the Vimeo
	 *
	 * @return string
	 */
	protected function _getHtmlString()
	{
		return '<iframe class="scribd_iframe_embed" src="http://www.scribd.com/embeds/%s/content" data-aspect-ratio="0.772727272727273" scrolling="no" id="%s" width="625" height="938" frameborder="0"></iframe>
			<script type="text/javascript">(function() { var scribd = document.createElement("script"); scribd.type = "text/javascript"; scribd.async = true; scribd.src = "http://www.scribd.com/javascripts/embed_code/inject.js"; var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(scribd, s); })();</script>';
	}
	
	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return 'http:\/\/www.scribd.com\/doc\/([0-9]{1,})\/[^\s]{1,}';
	}
}
