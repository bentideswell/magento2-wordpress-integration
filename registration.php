<?php
/*
 * @package  FishPig_WordPress
 * @author   Ben Tideswell (ben@fishpig.co.uk)
 * @url      https://fishpig.co.uk/magento/wordpress-integration/
 */
use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'FishPig_WordPress', __DIR__);

require __DIR__ . '/functions.php';
