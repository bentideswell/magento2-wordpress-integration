<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Youtube extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'youtube';
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
		if (preg_match_all('/\[youtube=(.*)\]/iU', $content, $matches)) {
			foreach($matches[1] as $key => $match) {
				$content = str_replace($matches[0][$key], sprintf('[%s url=%s]', $this->getTag(), str_replace('&', ' ', $match)), $content);
			}		
		}

		if (($shortcodes = $this->_getShortcodes($content)) !== false) {
			foreach($shortcodes as $shortcode) {
				$params = $shortcode->getParams();
				
				// Parse the URLs querystring
				parse_str(parse_url($params->getUrl(), PHP_URL_QUERY), $queryString);
				
				// Remove the URL from the params
				$params->unsetData('url');
				
				// Merge params with query string params
				$params = array_merge($params->getData(), $queryString);

				$url = 'https://www.youtube.com/embed/' . $params['v'];
				
				// Remove the v code from the params
				unset($params['v']);

				$url = rtrim($url . '?' . http_build_query($params), '?');

				$content = str_replace(
					$shortcode->getHtml(), 
					$this->_getYoutubeEmbedHtml($url, isset($params['w']) ? $params['w'] : null, isset($params['h']) ? $params['h'] : null),
					$content
				);
			}
		}
	}
	
	/**
	 * Retrieve the HTML pattern for the Vimeo
	 *
	 * @return string
	 */
	protected function _getYoutubeEmbedHtml($src, $width = null, $height = null)
	{
		return sprintf(
			'<iframe width="%s" height="%s" src="%s" frameborder="0" allowfullscreen></iframe>',
			$width ? $width : 560,
			$height ? $height : 315,
			$src
		);
	}

	/**
	 * Retrieve the content that goes between the shortcode tag and parsed URL
	 *
	 * @return string
	 */
	public function getConvertedUrlsMiddle()
	{
		return '=';
	}

	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return '(http[s]{0,1}:\/\/www.youtube.com\/watch\?.*)';
	}
}
