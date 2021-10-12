<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class ThemeTest implements \FishPig\WordPress\Api\Data\App\Integration\TestInterface
{
    /**
     * @param \FishPig\WordPress\App\ThemeResolver $themeResolver
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Theme\Resolver $themeResolver
    ) {
        $this->themeResolver = $themeResolver;
    }
    
    /**
     * @return void
     */
    public function runTest(): void
    {
        $theme = $this->themeResolver->getObject();

        if (!$theme->getRemoteHash()) {
            throw new IntegrationFatalException('The FishPig theme is not installed in WordPress. Run bin/magento fishpig:wordpress:build-theme');
        }
        
        if ($theme->getLocalHash() !== $theme->getRemoteHash()) {
            throw new IntegrationRecoverableException(
                'The WordPress FishPig theme has an update available. Run bin/magento fishpig:wordpress:build-theme'
            );
        }
    }
}
