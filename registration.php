<?php
/**
 * @package  FishPig_WordPress
 * @author   Ben Tideswell (ben@fishpig.co.uk)
 * @url      https://fishpig.co.uk/magento/wordpress-integration/
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'FishPig_WordPress', __DIR__);

if (!function_exists('__')) {
    $bootstrap    = BP . '/app/bootstrap.php';
    $canIncludeFpFunctions = true;

    if (strpos(file_get_contents($bootstrap), 'app/functions.php') !== false) {
        $appFunctions = BP . '/app/functions.php';
        $fpFunctions  = __DIR__ . '/functions.php';

        if (is_file($appFunctions)) {
            $canIncludeFpFunctions = md5_file($appFunctions) === md5_file($fpFunctions);
        }
    }

    if ($canIncludeFpFunctions) {
        require __DIR__ . '/functions.php';
    }
}
