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

class ThemeTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @param \FishPig\WordPress\App\ThemeResolver $themeResolver
     */
    public function __construct(
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \Magento\Backend\Model\Url $url,
        \Magento\Framework\App\State $appState,
        \FishPig\WordPress\App\Theme\PackageBuilder $themePackageBuilder,
        \FishPig\WordPress\App\Theme\PackageDeployer $themePackageDeployer,
        \FishPig\WordPress\App\Logger $logger,
        \FishPig\WordPress\App\DirectoryList $wpDirectoryList,
        \FishPig\WordPress\App\HTTP\RequestManager $requestManager,
        \FishPig\WordPress\Model\UrlInterface $wpUrl

    ) {
        $this->theme = $theme;
        $this->appMode = $appMode;
        $this->url = $url;
        $this->appState = $appState;
        $this->themePackageBuilder = $themePackageBuilder;
        $this->themePackageDeployer = $themePackageDeployer;
        $this->logger = $logger;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->requestManager = $requestManager;
        $this->wpUrl = $wpUrl;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        if ((!$this->theme->isInstalled() || !$this->theme->isLatestVersion()) && $this->appMode->isLocalMode()) {
            if ($this->buildAndDeployTheme()) {
                return;
            }
        }

        if (!$this->theme->isInstalled()) {
            throw new IntegrationFatalException(
                'The FishPig theme is not installed in WordPress. ' . $this->getErrorMessage()
            );
        }

        if (!$this->theme->isLatestVersion()) {
            throw new IntegrationFatalException(
                sprintf(
                    'The WordPress FishPig theme has an update available (latest=%s, current=%s). %s',
                    $this->theme->getLocalHash(),
                    $this->theme->getRemoteHash(),
                    $this->getErrorMessage()
                )
            );
        }
    }

    /**
     * Try to build and deploy the theme in local mode.
     *
     * @return bool
     */
    private function buildAndDeployTheme(): bool
    {
        try {
            if ($this->wpDirectoryList->isBasePathValid()) {
                $packageFile = $this->themePackageBuilder->getFilename();

                $this->themePackageDeployer->deploy($packageFile, $this->wpDirectoryList->getBasePath());

                // This activates the update in WordPress and sets the hash in the DB
                $this->requestManager->get($this->wpUrl->getSiteUrl('index.php?theme-activation'));

                return $this->theme->isLatestVersion();
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return false;
    }

    /**
     * @return string
     */
    private function getErrorMessage(): string
    {
        if (php_sapi_name() === 'cli' || $this->appState->getAreaCode() !== 'adminhtml') {
            return sprintf(
                'Run \'%s\' in the CLI to generate A ZIP archive of the theme and then install it in WordPress.',
                'bin/magento fishpig:wordpress:build-theme'
            );
        }

        return sprintf(
            '<a href="%s">Click here to download the theme</a> and then install it in WordPress.',
            $this->url->getUrl('wordpress/theme/build')
        );
    }
}
