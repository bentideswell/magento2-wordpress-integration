<?php
/**
 * @category    Fishpig
 * @package     Fishpig_Wordpress
 * @license     http://fishpig.co.uk/license.txt
 * @author      Ben Tideswell <help@fishpig.co.uk>
 */

namespace FishPig\WordPress\Helper;

class Filter extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 *
	 *
	 * @return 
	**/
	protected $_app = null;

	/**
	 *
	 *
	 * @return 
	**/
	public function __construct(
		\Magento\Framework\App\Helper\Context $context, 
		\FishPig\WordPress\Model\App $app,
		\FishPig\WordPress\Model\Config $config
	)
	{
		parent::__construct($context);
		
		$this->_app = $app;
		$this->_config = $config;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function process($string, $object = null)
	{
		$string = $this->addParagraphTagsToString($string);

		if ($shortcodes = $this->_config->getShortcodes()) {
			foreach($shortcodes as $alias => $class) {
				$string = (string)\Magento\Framework\App\ObjectManager::getInstance()
					->get($class)
						->setObject($object)
						->setValue($string)
						->process();
			}
		}

		return $string;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	public function addParagraphTagsToString($string)
	{
		if ($this->_getFunctionFromWordPress('wpautop', 'wp-includes' . DIRECTORY_SEPARATOR . 'formatting.php', array(
			'wpautop',
			'wp_replace_in_html_tags',
			'_autop_newline_preservation_helper',
			'wp_html_split',
			'get_html_split_regex',
		))) {
			$string = fp_wpautop($string);
			
			// Fix shortcodes that get P'd off!
			$string = preg_replace('/<p>\[/', '[', $string);
			$string = preg_replace('/\]<\/p>/', ']', $string);
		}

		return $string;
	}
	
	/**
	 *
	 *
	 * @return 
	**/
	protected function _getFunctionFromWordPress($function, $file, $depends = array())
	{
		$newFunction = 'fp_' . $function;
		
		if (function_exists($newFunction)) {
			return true;
		}

		$targetFile = $this->_app->getPath() . DIRECTORY_SEPARATOR . $file;

		if (!is_file($targetFile)) {
			return false;
		}
		
		$code = preg_replace('/\/\*\*.*\*\//Us', '', file_get_contents($targetFile));

		$depends = array_flip($depends);
		foreach($depends as $key => $value) {
			$depends[$key] = '';
		}

		foreach($depends as $function => $ignore) {
			if (preg_match('/(function ' . $function . '\(.*)function/sU', $code, $matches)) {
				$depends[$function] = $matches[1];
			}
			else {
				return false;
			}
		}
		
		$code = preg_replace('/(' . implode('|', array_keys($depends)) . ')/', 'fp_$1', implode("\n\n", $depends));
		
		@eval($code);

		return function_exists($newFunction);
	}
}
