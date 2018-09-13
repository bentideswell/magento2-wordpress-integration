<?php
/*
 *
 * WordPress
 * https://fishpig.co.uk/
 *
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
	ComponentRegistrar::MODULE,
	'FishPig_WordPress',
	__DIR__
);

/*
 *
 * Translation function fix
 *
 */
$functionsFile = BP . '/app/functions.php';

if (is_file($functionsFile)) {
	@file_put_contents($functionsFile, "<?php
/*
 * Translation function has been removed for WordPress Integration
 * This file is deprecated from Magento 2.3
 */
");
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
