<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

if (!defined('FISHPIG_SKIP_LEGACY_AUTOLOAD')) {
    spl_autoload_register(function($className) {
        $classTarget = 'FishPig\\WordPress\\';
        
        if (strpos($className, $classTarget) !== 0) {
            return false;
        }

        $classMap = [
            'FishPig\WordPress\Model\Factory',
            'FishPig\WordPress\Model\Homepage',
            'FishPig\WordPress\Model\IntegrationManager',
            'FishPig\WordPress\Model\Logger',
            'FishPig\WordPress\Model\Logger\Handler',
            'FishPig\WordPress\Model\OptionManager',
            'FishPig\WordPress\Model\ResourceConnection',
            'FishPig\WordPress\Model\Plugin',
            'FishPig\WordPress\Model\PostTypeManager',
            'FishPig\WordPress\Model\PostTypeManager\Proxy',
            'FishPig\WordPress\Model\ShortcodeManager',
            'FishPig\WordPress\Model\TaxonomyManager',
            'FishPig\WordPress\Model\Theme',
            'FishPig\WordPress\Model\Url',
            'FishPig\WordPress\Model\WPConfig',
        ];

        if (in_array($className, $classMap)) {
            $legacyClassFile = __DIR__ . str_replace('\\', '/', str_replace($classTarget, '/legacy-src\\', $className)) . '.php';
  
            if (is_file($legacyClassFile)) {
                require_once $legacyClassFile;
                return true;
            }
        }

        return false;
    });
}
