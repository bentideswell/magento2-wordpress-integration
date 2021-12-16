<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class RemoteHashProvider implements \FishPig\WordPress\Api\App\Theme\HashProviderInterface
{
    /**
     * @param \FishPig\WordPress\App\Option $optionDataSource
     */
    public function __construct(
        \FishPig\WordPress\App\Option $optionDataSource
    ) {
        $this->optionDataSource = $optionDataSource;
    }

    /**
     * Retrieve the hash using the option data source directly
     * This is used over the OptionRepository so that if the theme hash requires an update
     * We can get the hash again without the caching that the OptionRepository adds and check if it's changed
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->optionDataSource->get('fishpig-theme-hash') ?: '';
    }
}
