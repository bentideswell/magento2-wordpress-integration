<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'FishPig_WordPress', __DIR__);

// Protection against installing module in incorrect directory
$currentLocation = __DIR__;

if (strpos($currentLocation, 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR) !== false) {
    $relativeLocation = 'app' . DIRECTORY_SEPARATOR . 'code' . DIRECTORY_SEPARATOR . 'FishPig' . DIRECTORY_SEPARATOR . 'WordPress';

    if (strpos($currentLocation, $relativeLocation) === false) {
        throw new \Exception(sprintf(
            "%s is installed in the wrong folder. Please install the module in %s and make sure that you use the correct capitalisation of the module name (%s).",
            "FishPig_WordPress",
            $relativeLocation,
            'FishPig' . DIRECTORY_SEPARATOR . 'WordPress'
        ));
    }
}

/**
 * This fixes an issue with generated classes that have an underscore in the module name
 * This stops Magento constantly trying to recreate the file
 */
spl_autoload_register(function($className) {
    if (strpos($className, 'FishPig\\WordPress_') !== false) {
        if (preg_match('/(Factory|Interceptor|Proxy)$/', $className)) {
            $filePath = BP . '/generated/code/' . str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className) . '.php';

            if (is_file($filePath)) {
                include_once $filePath;
            }
        }
    }
});
