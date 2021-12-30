<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
namespace FishPig\WordPress\X;

class Helper
{
    /**
     * @return string
     */
    static public function getCurrentUrl(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http")
                . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    }

}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento