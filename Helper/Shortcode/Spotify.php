<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

class Fishpig_Wordpress_Helper_Shortcode_Spotify extends Fishpig_Wordpress_Helper_Shortcode_Abstract
{
	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	public function getTag()
	{
		return 'spotify';
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
		// Convert URLs to spotify strings
		if (preg_match_all('/http[s]{0,1}:\/\/(open|play|embed).spotify.com\/(.*)[\n]{1,}/U', "\n" . $content . "\n", $matches)) {
			foreach($matches[2] as $it => $value) {
				$content = str_replace(trim(strip_tags($matches[0][$it])), 'spotify:' . str_replace('/', ':', trim(strip_tags($value))), $content);
			}
		}

		// Convert Spotify URLs to iframes
		if (preg_match_all('/[\s]{1,}(spotify:.*)[\s<]{1,}/U', $content, $matches)) {
			foreach($matches[1] as $it => $value) {
				$content = str_replace($value, $this->_getSpotifyEmbedHtml($value), $content);
			}
		}
		
		// Clean up the <p> tags around the iframes that aren't needed
		$content = preg_replace('/<p>[\s]{0,}(<iframe.*><\/iframe>)[\s]{0,}<\/p>/U', '$1', $content);

		return $this;
	}

	/**
	 * Retrieve the HTML pattern for the Vimeo
	 *
	 * @return string
	 */
	protected function _getSpotifyEmbedHtml($src)
	{
		return sprintf(
			'<iframe src="https://embed.spotify.com/?uri=%s" width="300" height="380" frameborder="0" allowtransparency="true"></iframe>',
			$src
		);
	}
}
