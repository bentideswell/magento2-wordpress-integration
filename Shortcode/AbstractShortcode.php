<?php
/*
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Shortcode;

use \FishPig\WordPress\Model\App;
use \Magento\Framework\View\Element\Context;

abstract class AbstractShortcode
{
	/*
	 * Regular expression patterns for identifying shortcodes and parameters
	 *
	 * @const string
	 */
	const EXPR_SHOTRCODE_OPEN_TAG = '(\[{{shortcode}}[^\]]{0,}\])';
	const EXPR_SHOTRCODE_CLOSE_TAG = '(\[\/{{shortcode}}[^\]]{0,}\])';
	
  /*
   * @var array
   */
  protected $_data = [];
    
	/*
	 * @var FishPig\WordPress\Model\App
	 */
	protected $_app;
	
	/*
	 * @var Magento\Framework\View\Layout
	 */
	protected $_layout = null;
	
	/*
	 * @var Magento\Framework\View\Layout
	 */
	protected $_factory = null;
	
	/*
	 * @var Magento\Framework\View\Layout
	 */
	protected $_cache = null;
	
	/*
	 * @var Magento\Framework\View\Layout
	 */
	protected $_cacheState = null;

	/*
	 * Function that handles generating the HTML
	 *
	 * @return $this
	 */
	abstract protected function _process();
	
	/*
	 * Constructor
	 */
  public function __construct(App $app, Context $context)
  {
		$this->_app = $app;
		$this->_factory = $app->getFactory();
		$this->_layout = $context->getLayout();
		$this->_cache = $context->getCache();
		$this->_cacheState = $context->getCacheState();
  }
    
  /*
	 * Generate the HTML and return it
	 *
	 * @return string
	 */
	public function process()
	{
		$this->_process();
		
		return (string)$this->getValue();
	}
	
  /*
	 * Find the shortcodes for $tag
	 *
	 * @return array|false
	 */
	protected function _getShortcodesByTag($tag)
	{
		$shortcodes = array();
		$content = $this->getValue();
		
		if (strpos($content, '[' . $tag) !== false) {
			$hasCloser = strpos($content, '[/' . $tag . ']') !== false;
			$open = str_replace('{{shortcode}}', $tag, self::EXPR_SHOTRCODE_OPEN_TAG);

			if ($hasCloser) {
				$close = str_replace('{{shortcode}}', $tag, self::EXPR_SHOTRCODE_CLOSE_TAG);

				if (preg_match_all('/' . $open . '(.*)' . $close . '/iUs', $content, $matches)) {
					foreach($matches[0] as $matchId => $match) {
						$shortcodes[] = new \Magento\Framework\DataObject(array(
							'html' => $match,
							'opening_tag' => $matches[1][$matchId],
							'inner_content' => $matches[2][$matchId],
							'closing_tag' => $matches[3][$matchId],
							'params' => $this->_parseShortcodeParameters($matches[1][$matchId]),
						));
					}
				}
			}
			else if (preg_match_all('/' . $open . '/iU', $content, $matches)) {
				foreach($matches[0] as $matchId => $match) {
					$shortcodes[] = new \Magento\Framework\DataObject(array(
						'html' => $match,
						'opening_tag' => $matches[1][$matchId],
						'params' => $this->_parseShortcodeParameters($matches[1][$matchId]),
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
	protected function _parseShortcodeParameters($openingTag)
	{
		$parameters = array();

		if (($regex = trim($this->getParameterRegex())) !== '') {
			$openingTag = trim(substr(trim($openingTag), strlen($this->getTag())+1), '[] ');
			
			if (preg_match_all($regex, $openingTag, $matches)) {
				foreach($matches[2] as $key => $value) {
					$parameters[trim($matches[1][$key])] = trim($value, '"\' ');
					$openingTag = str_replace($matches[0][$key], '', $openingTag);
				}
			}
		
			if ($this->getShortcodeIdKey() !== '') {
				foreach(explode(' ', trim($openingTag, ' ')) as $value) {
					if (($value = trim($value)) !== '') {
						$parameters = array_merge(array($this->getShortcodeIdKey() => $value), $parameters);
						break;
					}
				}
			}
		}

		return new \Magento\Framework\DataObject($parameters);
	}

	/*
	 * Retrieve the parameter regex
	 *
	 * @return string
	 */
	public function getParameterRegex()
	{
		return '/([a-z]{1,})=([^\s ]{1,})/i';
	}
	
    /*
	 * Get the post ID if the post is set
	 *
	 * @return int|false
	 */
	public function getPostId()
	{
		return $this->getObject() ? (int)$this->getObject()->getId() : false;
	}

	/*
	 * Set the target object
	 *
	 * @param mixed $object
	 * @return $this
	 */
	public function setObject($object)
	{
		$this->_data['object'] = $object;
		
		return $this;
	}
	
	/*
	 * @return mixed
	 */
	public function getObject()
	{
		return isset($this->_data['object']) ? $this->_data['object'] : false;
	}
	
	/*
	 * Set the target value
	 *
	 * @param mixed value
	 * @return $this
	 */
	public function setValue($value)
	{
		$this->_data['value'] = $value;
		
		return $this;
	}

	/*
	 * @return mixed
	 */	
	public function getValue()
	{
		return isset($this->_data['value']) ? $this->_data['value'] : false;
	}

	/*
	 * @return string
	 */	
	public function getShortcodeIdKey()
	{
		return '';
	}
	
	/**
	 * Determine whether shortcode requires asset injection
	 *
	 * @param string $content = null
	 * @return bool
	 */
	public function requiresAssetInjection()
	{
		return false;
	}
	
	/*
	 * Check if the plugin associated with the shortcode is enabled
	 * For default shortcodes, they don't rely on a plugin so return true
	 *
	 * @return bool
	 */
	public function isPluginEnabled()
	{
		return true;
	}
}
