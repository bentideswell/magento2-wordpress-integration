<?php
/*
 *
 * Ensure proper translation in Magento and WordPress
 *
 */
if (!function_exists('__')) {
	function __()
	{
		$argc = func_get_args();
		$text = array_shift($argc);

		if (!empty($argc) && is_array($argc[0])) {
			$argc = $argc[0];
		}
	
		if (isset($GLOBALS['phrase_as_string'])) {
			return (string)new \Magento\Framework\Phrase($text, $argc);
		}
	
		return new \Magento\Framework\Phrase($text, $argc);
	}
}
