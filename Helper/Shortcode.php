<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Helper;

use Magento\Framework\DataObject;

class Shortcode
{	
  /*
	 * Find the shortcodes for $tag
	 *
	 * @return array|false
	 */
	public function getShortcodesByTag($content, $tag)
	{
		$shortcodes = array();
		
		if (strpos($content, '[' . $tag) !== false) {
			$hasCloser = strpos($content, '[/' . $tag . ']') !== false;
			$open = str_replace('{{shortcode}}', $tag, '(\[{{shortcode}}[^\]]{0,}\])');

			if ($hasCloser) {
				$close = str_replace('{{shortcode}}', $tag, '(\[\/{{shortcode}}[^\]]{0,}\])');

				if (preg_match_all('/' . $open . '(.*)' . $close . '/iUs', $content, $matches)) {
					foreach($matches[0] as $matchId => $match) {
						$shortcodes[] = new DataObject(array(
							'html' => $match,
							'opening_tag' => $matches[1][$matchId],
							'inner_content' => $matches[2][$matchId],
							'closing_tag' => $matches[3][$matchId],
							'params' => $this->parseShortcodeParameters($matches[1][$matchId], $tag),
						));
					}
				}
			}
			else if (preg_match_all('/' . $open . '/iU', $content, $matches)) {
				foreach($matches[0] as $matchId => $match) {
					$shortcodes[] = new DataObject(array(
						'html' => $match,
						'opening_tag' => $matches[1][$matchId],
						'params' => $this->parseShortcodeParameters($matches[1][$matchId], $tag),
					));
				}
			}
		}
		
		return count($shortcodes) > 0 ? $shortcodes : false;
	}
	
	/*
	 * Extract parameters from a shortcode opening tag
	 *
	 * @param string $openingTag
	 * @return array
	 */
	protected function parseShortcodeParameters($openingTag, $tag)
	{
		$parameters = [];
		$openingTag = trim(substr(trim($openingTag), strlen($tag)+1), '[] ');
		
		if (preg_match_all('/([a-z]{1,})=([^\s ]{1,})/i', $openingTag, $matches)) {
			foreach($matches[2] as $key => $value) {
				$parameters[trim($matches[1][$key])] = trim($value, '"\' ');
				$openingTag = str_replace($matches[0][$key], '', $openingTag);
			}
		}

		return new DataObject($parameters);
	}

	/*
	 * Extract any inline JS in $content
	 * and remove it from $content
	 *
	 * @param string $content
	 * @return array
	 */
	public function extractInlineJs(&$content)
	{
		if (!preg_match_all('/(<script[^>]{0,}>)(.*)(<\/script>)/Us', $content, $matches)) {	
			return [];
		}
		
		$inline = [];
		
		foreach($matches[0] as $match) {
			$inline[] = $match;
			$content = str_replace($match, '', $content);
		}

		return $inline;
	}
	
	/*
	 * Clean the array of assets
	 *
	 * @param array $assets
	 * @return array|false
	 */
	public function cleanAssetArray($assets)
	{
		if (!is_array($assets)) {
			return $assets;
		}

		$buffer = [];
		
		foreach($assets as $asset) {
			if (is_array($asset)) {
				foreach($this->cleanAssetArray($asset) as $line) {
					$buffer[] = $line;
				}
			}
			else if (trim($asset)) {
				$buffer[] = trim($asset);
			}
		}
		
		return $buffer;
	}
}
