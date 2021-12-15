<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Plugin
{
    /**
     * @param \FishPig\WordPress\App\Plugin $plugin
     * @param \FishPig\WordPress\App\Option $option
     */
    public function __construct(
        \FishPig\WordPress\App\Plugin $plugin,
        \FishPig\WordPress\App\Option $option
    ) {
        $this->plugin = $plugin;
        $this->option = $option;
    }

    /**
     * @return bool
     */
    public function install($target, $source, $enable = false)
    {
        throw new \Exception("It's not possible to automatically install a WP plugin.");
    }

    /**
     * @param  string $plugin
     * @return bool
     */
    public function enable($plugin)
    {
        throw new \Exception("It's not possible to automatically enable a WP plugin.");
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function isEnabled($name): bool
    {
        return $this->plugin->isEnabled($name);
    }

    /**
     * @param  string $plugin
     * @param  string $key = null
     * @return mixed
     */
    public function getOption($plugin, $key = null)
    {
        $options = $this->option->getOption($plugin);

        if (($data = @unserialize($options)) !== false) {
            if (is_null($key)) {
                return $data;
            }

            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return $options;
    }
}
