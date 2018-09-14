<?php
/**
 * @package  FishPig_WordPress
 * @author   Ben Tideswell (ben@fishpig.co.uk)
 * @url      https://fishpig.co.uk/magento/wordpress-integration/
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
	ComponentRegistrar::MODULE,
	'FishPig_WordPress',
	__DIR__
);

/**
 * Translation function fix
 * If app/functions.php exists (Magento 2.2 and below)
 * Swap it for our version (the same file with a call to function_exists)
 */
$functionsFile = BP . '/app/functions.php';

if (is_file($functionsFile)) {
	$legacyFunctions = @file_get_contents($functionsFile);
	$fpFunctions     = @file_get_contents(__DIR__ . '/functions.php');
	
	if (strpos($legacyFunctions, 'function_exists') === false) {
		@file_put_contents($functionsFile, $fpFunctions);
	}
}

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
