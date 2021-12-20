<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class PluginManager
{
    /**
     * @var \FishPig\WordPress\App\Plugin
     */
    private $dataSource = null;

    /**
     * @param  \FishPig\WordPress\App\Plugin $dataSource
     */
    public function __construct(
        \FishPig\WordPress\App\Plugin $dataSource
    ) {
        $this->dataSource = $dataSource;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function isEnabled(string $pluginName): bool
    {
        return in_array($pluginName, $this->dataSource->getActivePlugins());
    }
}
