<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class PluginsTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\PluginManager $pluginManager
    ) {
        $this->pluginManager = $pluginManager;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        foreach (['hello.php', 'elementor/elementor.php', 'fake/fake.php'] as $plugin) {
            $this->pluginManager->isEnabled($plugin);
        }
    }
}
