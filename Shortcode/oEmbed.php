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
	 * Constructor
	**/
    public function __construct(
	    \FishPig\WordPress\Model\App $app,
    	\Magento\Framework\View\Element\Context $context, 
	    \Magento\Framework\HTTP\Client\Curl $curl
    )
    {
	    parent::__construct($app, $context);
		
		$this->_curl = $curl;
    }
    
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
		$cacheKey = md5($url);
		$cacheIsActive = $this->_cacheState->isEnabled(
			\Magento\Framework\App\Cache\Type\Block::TYPE_IDENTIFIER
		);

		
		if ($cacheIsActive) {
			if ($data = $this->_cache->load($cacheKey)) {
				return $data;
			}
		}
		
		$data = false;
		$this->_curl->get($url);
		
		if (($data =  $this->_curl->getBody()) && $cacheIsActive) {
			$this->_cache->save($data, $cacheKey, array('FishPig', 'WordPress', 'oEmbed'), 604800);
		}
		
		return $data;
	}
}
