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
            \FishPig\WordPress\Block\Homepage\View::class,
            \FishPig\WordPress\Helper\Autop::class,
            \FishPig\WordPress\Helper\Core::class,
            \FishPig\WordPress\Model\Factory::class,
            \FishPig\WordPress\Model\Homepage::class,
            \FishPig\WordPress\Model\IntegrationManager::class,
            \FishPig\WordPress\Model\Logger::class,
            \FishPig\WordPress\Model\Logger\Handler::class,
            \FishPig\WordPress\Model\OptionManager::class,
            \FishPig\WordPress\Model\ResourceConnection::class,
            \FishPig\WordPress\Model\Plugin::class,
            \FishPig\WordPress\Model\PostTypeManager::class,
            \FishPig\WordPress\Model\PostTypeManager\Proxy::class,
            \FishPig\WordPress\Model\ShortcodeManager::class,
            \FishPig\WordPress\Model\TaxonomyManager::class,
            \FishPig\WordPress\Model\Theme::class,
            \FishPig\WordPress\Model\Url::class,
            \FishPig\WordPress\Model\WidgetManager::class,
            \FishPig\WordPress\Model\WPConfig::class
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
// phpcs:ignoreFile