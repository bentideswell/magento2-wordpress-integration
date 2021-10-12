<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Theme;

class Resolver extends \FishPig\WordPress\App\Integration\Mode\ObjectResolver
{
    /**
     * @return \FishPig\WordPress\Api\Data\App\Integration\ThemeInterface
     */
    public function getTheme(): \FishPig\WordPress\Api\Data\App\Integration\ThemeInterface
    {
        return $this->getObject();
    }
}
