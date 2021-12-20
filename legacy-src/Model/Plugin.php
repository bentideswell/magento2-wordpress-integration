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
     * @param \FishPig\WordPress\Model\PluginManager $pluginManager
     * @param \FishPig\WordPress\Model\OptionRepository $optionRepository
     */
    public function __construct(
        \FishPig\WordPress\Model\PluginManager $pluginManager,
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->pluginManager = $pluginManager;
        $this->optionRepository = $optionRepository;
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
        return $this->pluginManager->isEnabled($name);
    }

    /**
     * @param  string $plugin
     * @param  string $key = null
     * @return mixed
     */
    public function getOption($plugin, $key = null)
    {
        if ($data = $this->optionRepository->getUnserialized($plugin)) {
            if (is_null($key)) {
                return $data;
            }

            return isset($data[$key])
                ? $data[$key]
                : null;
        }

        return false;
    }
}
