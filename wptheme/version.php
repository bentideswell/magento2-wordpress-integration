<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\X;

class Version
{
    /**
     * @string
     */
    static private $version = null;

    /**
     * @return ?string
     */
    static public function getVersion(): ?string
    {
        if (self::$version === null) {

            self::$version = false;
            $sourceFile = __DIR__ . '/../style.css';

            if (is_file($sourceFile)) {
                $sourceData = file_get_contents($sourceFile);

                if (preg_match('/Version:\s*([a-z0-9]+)/', $sourceData, $versionMatch)) {
                    self::$version = $versionMatch[1];
                }
            }
        }

        return self::$version ?: null;
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
