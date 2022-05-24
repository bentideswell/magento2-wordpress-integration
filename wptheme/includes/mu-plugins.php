<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
namespace FishPig\WordPress\X;

class MuPlugins
{
    /**
     * @const string
     */
    const PLUGIN_FILENAME = 'fishpig-wp.php';

    /**
     *
     */
    public function __construct()
    {
        add_action(
            'fishpig/wordpress/theme/updated',
            [$this, 'registerMuPlugin']
        );   
    }
    
    public function registerMuPlugin()
    {
        $ds = DIRECTORY_SEPARATOR;
        $sourceDir = dirname(__DIR__) . $ds . 'mu-plugins';
        $targetDir = ABSPATH . 'wp-content' . $ds . 'mu-plugins';
        $sourceFiles = [];
        
        if (!is_dir($sourceDir)) {
            return $this->deleteMuPlugin();
        }
        
        foreach (scandir($sourceDir) as $sourceDirItem) {
            if ($sourceDirItem === '.' || $sourceDirItem === '..') {
                continue;
            }
            
            $sourceFile = $sourceDir . $ds . $sourceDirItem;
            
            if (is_file($sourceFile) && substr($sourceFile, -4) === '.php') {
                $sourceFiles[] = str_replace(ABSPATH, '', $sourceFile);
            }
        }
            
        if (!$sourceFiles) {
            return $this->deleteMuPlugin();
        }

        $muPluginLines = [
            "<?php
/**
 * Plugin Name: FishPig WordPress Integration
 * Description: Plugin required to facilitate Magento integration. Auto installed
 * Plugin URI: https://fishpig.com/
 * Author: FishPig
 * Version: 1.0.0
 * Author URI: https://fishpig.com/
 * Text Domain: fishpig
 *
 */
 "
        ];

        foreach ($sourceFiles as $sourceFile) {
            $muPluginLines[] = "if (is_file(ABSPATH . '" . $sourceFile . "')) {
    require_once ABSPATH . '" . $sourceFile . "';
}";
        }
        
        $muPluginContent = implode("\n", $muPluginLines) . "\n";
        $muPluginFile = $this->getPluginFile();
        $muPluginDir = dirname($muPluginFile);
        
        if (!is_dir($muPluginDir)) {
            @mkdir($muPluginDir);
            
            if (!is_dir($muPluginDir)) {
                throw new \RuntimeException('Unable to create mu-plugins directory at ' . $muPluginDir);
            }
        }
        
        @file_put_contents($muPluginFile, $muPluginContent);
        
        if (!is_file($muPluginFile)) {
            throw new \RuntimeException('Unable to create mu-plugin file at ' . $muPluginFile);
        }
    }    
    
    /**
     * @return string
     */
    public function getPluginFile()
    {
        return ABSPATH . 'wp-content/mu-plugins/' . self::PLUGIN_FILENAME;;
    }
    
    /**
     *
     */
    public function deleteMuPlugin()
    {
        $muPluginFile = $this->getPluginFile();
        
        if (is_file($muPluginFile)) {
            @unlink($muPluginFile);
            
            if (is_file($muPluginFile)) {
                throw new \RuntimeException('Unable to delete mu-plugin file at ' . $muPluginFile);
            }
        }
    }
}
// phpcs:ignoreFile -- this file is a WordPress theme file and will not run in Magento