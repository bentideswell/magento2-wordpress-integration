<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

interface PluginManagerInterface
{
    /**
     * @param  string $name
     * @return bool
     */
    public function isEnabled(string $name): bool;
}
