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
/**
 * Create value-object \Magento\Framework\Phrase
 * @deprecated The global function __() is now loaded via Magento Framework, the below require is only
 *             for backwards compatibility reasons and this file will be removed in a future version
 * @see        Magento\Framework\Phrase\__.php
 * @SuppressWarnings(PHPMD.ShortMethodName)
 * @return \Magento\Framework\Phrase
 */
if (!function_exists('__')) {
	function __()
	{
	    $argc = func_get_args();
	
	    $text = array_shift($argc);
	    if (!empty($argc) && is_array($argc[0])) {
	        $argc = $argc[0];
	    }
	
	    return new \Magento\Framework\Phrase($text, $argc);
	}
}
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
