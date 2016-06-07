<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

abstract class Fishpig_Wordpress_Helper_Shortcode_Abstract extends Fishpig_Wordpress_Helper_Abstract
{
	/**
	 * Regular expression patterns for identifying
	 * shortcodes and parameters
	 *
	 */
	const EXPR_SHOTRCODE_OPEN_TAG = '(\[{{shortcode}}[^\]]{0,}\])';
	const EXPR_SHOTRCODE_CLOSE_TAG = '(\[\/{{shortcode}}[^\]]{0,}\])';

	/**
	 * Retrieve the shortcode tag
	 *
	 * @return string
	 */
	abstract public function getTag();
	
	/**
	 * Apply the shortcode to the content
	 *
	 * @param string $content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return void
	 */
	abstract protected function _apply(&$content, Fishpig_Wordpress_Model_Post $post);

	/**
	 * Apply the shortcode to the content
	 *
	 * @param string $content
	 * @param Fishpig_Wordpress_Model_Post $post
	 * @return void
	 */	
	public function apply(&$content, Fishpig_Wordpress_Model_Post $post)
	{
		if (strpos($content, $this->getTag()) === false) {
			return $this;
		}

		$content = "\n"  . $content . "\n";
		
		try {
			$this->_convertTagAliases($content);
			$this->_convertInnerUrls($content);
			$this->_convertRawUrls($content);

			return $this->_apply($content, $post);
		}
		catch (Exception $e) {
			$this->log($e);
		}
	}	

	public function getTagAliases()
	{
		return false;
	}
	
	/**
	 * Retrieve the shortcode ID key
	 *
	 * @return string
	 */
	public function getShortcodeIdKey()
	{
		return '';
	}
	
	/**
	 * Retrieve the regex pattern for the inner URL's
	 *
	 * @return string
	 */
	public function getInnerUrlsRegex()
	{
		return '';
	}
	
	/**
	 * Retrieve the regex pattern for the raw URL's
	 *
	 * @return string
	 */
	public function getRawUrlRegex()
	{
		return '';
	}
	
	/**
	 * Retrieve the parameter regex
	 *
	 * @return string
	 */
	public function getParameterRegex()
	{
		return '/([a-z]{1,})=([^\s ]{1,})/i';
	}
	
	/**
	 * Retrieve the content that goes between the shortcode tag and parsed URL
	 *
	 * @return string
	 */
	public function getConvertedUrlsMiddle()
	{
		return ' ';
	}
	
	/**
	 * Retrieve the HTML string
	 * This is usually parsed using sprintf
	 *
	 * @return string
	 */
	protected function _getHtmlString()
	{
		return '';
	}
	
	/**
	 * Convert inner URL's to the shortcode
	 *
	 * @param string $content
	 * @return void
	 */
	protected function _convertInnerUrls(&$content)
	{
		if (($regex = $this->getInnerUrlsRegex()) !== '') {
			if (preg_match_all('/' . sprintf($regex, $this->getTag()) . '/i', $content, $result)) {		
				foreach($result[1] as $key => $code) {
					$content = str_replace($result[0][$key], sprintf('[%s %s %s]', $this->getTag(), rtrim($code, '/'), $result[2][$key]), $content);
				}
			}
		}
	}

	/**
	 * Convert raw URL's to the shortcode
	 *
	 * @param string $content
	 * @return void
	 */
	protected function _convertRawUrls(&$content)
	{
		$content = "\n" . $content . "\n";

		if (($regex = $this->getRawUrlRegex()) !== '') {
			$regexes = array(
				'[\r\n]{1}' . $regex . '[\r\n]{1}',
				'<p>[\s]{0,}' . $regex . '[\s]{0,}<\/p>',
			);
			
			foreach($regexes as $regex) {
				if (preg_match_all('/' . $regex . '/i', $content, $result)) {
					foreach($result[1] as $key => $url) {
						$content = str_replace(
							ltrim($result[0][$key], '>'), 
							sprintf('[%s%s%s]', $this->getTag(), $this->getConvertedUrlsMiddle(), trim(strip_tags($url))), 
							$content
						);
					}
				}
			}
		}
	}
	
	/**
	 * Convert tag aliases to the correct tag
	 *
	 * @param string $content
	 * @return void
	 */
	protected function _convertTagAliases(&$content)
	{
		if (($aliases = $this->getTagAliases()) !== false) {
			$tag = $this->getTag();
			
			foreach($aliases as $alias) {
				$content = str_replace(array('[' . $alias, '[/' . $alias . ']'), array('[' . $this->getTag(), '[/' . $this->getTag() . ']'), $content);	
			}
		}
	}
	
	/**
	 * Extract shortcodes from a string
	 *
	 * @param string $content
	 * @return false|array
	 */
	protected function _getShortcodes($content)
	{
		$shortcodes = array();

		if (strpos($content, '[' . $this->getTag()) !== false) {
			$hasCloser = strpos($content, '[/' . $this->getTag() . ']') !== false;
			$open = str_replace('{{shortcode}}', $this->getTag(), self::EXPR_SHOTRCODE_OPEN_TAG);

			if ($hasCloser) {
				$close = str_replace('{{shortcode}}', $this->getTag(), self::EXPR_SHOTRCODE_CLOSE_TAG);

				if (preg_match_all('/' . $open . '(.*)' . $close . '/iUs', $content, $matches)) {
					foreach($matches[0] as $matchId => $match) {
						$shortcodes[] = new Varien_Object(array(
							'html' => $match,
							'opening_tag' => $matches[1][$matchId],
							'inner_content' => $matches[2][$matchId],
							'closing_tag' => $matches[3][$matchId],
							'params' => new Varien_Object($this->_parseShortcodeParameters($matches[1][$matchId])),
						));
					}
				}
			}
			else if (preg_match_all('/' . $open . '/iU', $content, $matches)) {
				foreach($matches[0] as $matchId => $match) {
					$shortcodes[] = new Varien_Object(array(
						'html' => $match,
						'opening_tag' => $matches[1][$matchId],
						'params' => new Varien_Object($this->_parseShortcodeParameters($matches[1][$matchId])),
					));
				}
			}
		}
		
		if (count($shortcodes) > 0) {
			return $shortcodes;
		}
		
		return false;
	}
	
	/**
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

		return $parameters;
	}
	
	/**
	 * Wrapper for preg_match that adds extra functionality
	 *
	 * @param string $pattern
	 * @param string $value
	 * @param int $keyToReturn
	 * @return mixed
	 */
	public function _match($pattern, $value, $keyToReturn = -1, $default = false)
	{
		$result = array();
		preg_match($pattern, $value, $result);
		
		if ($keyToReturn == -1) {
			return $result;
		}

		return isset($result[$keyToReturn]) ? $result[$keyToReturn] : $default;
	}

	/**
	 * Shortcut to create a block
	 *
	 * @param string $type
	 * @param string $name = null
	 * @return Mage_Core_Block_Abstract
	 */
	public function _createBlock($type, $name = null)
	{
		return Mage::getSingleton('core/layout')->createBlock($type, $name.microtime());
	}	
	
	/**
	 * Retrieve the template
	 *
	 * @param Varien_Object $params
	 * @return string
	 */
	protected function _getTemplate(Varien_Object $params)
	{
		return $params->getTemplate() ? $params->getTemplate() : $this->_getDefaultTemplate();
	}
	
	/**
	 * Retrieve the default template
	 *
	 * @return string
	 */
	protected function _getDefaultTemplate()
	{
		return '';
	}
}
