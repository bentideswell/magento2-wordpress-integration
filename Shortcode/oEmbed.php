<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Shortcode;

class oEmbed extends AbstractShortcode
{
	/**
	 *
	 *
	 * @return 
	**/
	public function getTag()
	{
		return '';
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	protected function _getSources()
	{
		return array(
			array(
				'regex' => 'http[s]{0,1}:\/\/www.youtube.com\/watch\?.*',
				'endpoint' => 'https://www.youtube.com/oembed?url=%s',
			),
			array(
				'regex' => 'http[s]{0,1}:\/\/vimeo.com\/.*',
				'endpoint' => 'https://vimeo.com/api/oembed.json?url=%s',
			),
		);
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	protected function _process()
	{
		if ($sources = $this->_getSources()) {
			$value = "\n" . $this->getValue() . "\n";

			foreach($sources as $source) {
				if (preg_match_all('/\n(' . $source['regex'] . ')\n/', $value, $matches)) {
					$urls = $matches[1];
					
					foreach($urls as $url) {
						$embedUrl = sprintf($source['endpoint'], trim($url));
						
						if ($data = $this->_getOembedData($embedUrl)) {
							$data = json_decode($data, true);
							$value = str_replace($url, $data['html'], $value);
						}
					}
				}
			}
			
			$this->setValue($value);
		}
		
		
		return $this;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	protected function _getOembedData($url)
	{
		return file_get_contents($url);
	}
}
