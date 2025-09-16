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
     * 
     */
    static private $tags = null;

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


    /**
     *
     */
    static public function getTags(): array
    {
        if (self::$tags === null) {
            self::$tags = [];
            $sourceFile = __DIR__ . '/../style.css';

            if (is_file($sourceFile)) {
                $sourceData = file_get_contents($sourceFile);

                if (preg_match('/Tags:\s*(.*)\n/U', $sourceData, $tagsMatch)) {
                    self::$tags = array_map('trim', explode(',', $tagsMatch[1]));
                }
            }
        }

        return self::$tags;
    }

    /**
     *
     */
    static public function getAddonTags(): array
    {
        return array_filter(
            self::getTags(),
            function ($tag) {
                if ($tag === 'FishPig' || $tag === 'Magento 2') {
                    return false;
                }

                return true;
            }
        );
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento
