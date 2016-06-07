<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Gist extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'gist';
	}
	
	/**
	 * Retrieve the shortcode ID key
	 *
	 * @return string
	 */
	public function getShortcodeIdKey()
	{
		return 'url';
	}
	
	/**
	 * Apply the shortcode to the content
	 *
	 * @param string $content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return void
	 */	
	public function apply(&$content, Fishpig_Wordpress_Model_Post $post)
	{
		$content = preg_replace('/(\[' . $this->getTag() . ' .*)([ ]{0,1}\/)(\])/iU', '$1$3', $content);
		
		return parent::apply($content, $post);
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
				if ($url = $shortcode->getParams()->getUrl()) {
					$content = str_replace($shortcode->getHtml(), sprintf($this->_getHtmlString(), $url), $content);
				}
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
		return '<script type="text/javascript" src="%s.js"></script>';
	}
	
	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return '(http[s]{0,1}:\/\/gist\.github\.com\/[0-9]{1,})\s';
	}
}
