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
        \FishPig\WordPress\App\Theme $theme,
        \Magento\Backend\Model\Url $url
    ) {
        $this->theme = $theme;
        $this->url = $url;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        if (php_sapi_name() === 'cli') {
            $errorMsg = sprintf(
                'Run \'%s\' in the CLI to generate the theme and then install it in WordPress.',
                'bin/magento fishpig:wordpress:build-theme'
            );
        } else {
            $errorMsg = sprintf(
                '<a href="%s">Click here to download the theme</a> and then install it in WordPress.',
                $this->url->getUrl('wordpress/theme/build')
            );
        }

        if (!$this->theme->isInstalled()) {
            throw new IntegrationFatalException('The FishPig theme is not installed in WordPress. ' . $errorMsg);
        }

        if (!$this->theme->isLatestVersion()) {
            throw new IntegrationRecoverableException('The WordPress FishPig theme has an update available. ' . $errorMsg);
        }
    }
}
