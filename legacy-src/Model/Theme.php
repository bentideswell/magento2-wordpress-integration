<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Theme
{
    /**
     * @param \FishPig\WordPress\Model\Config $config
     */
    public function __construct(
        \FishPig\WordPress\Model\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @return $this
     */
    public function validate()
    {
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isThemeIntegrated()
    {
        return $this->config->isThemeIntegrationEnabled();
    }
}
