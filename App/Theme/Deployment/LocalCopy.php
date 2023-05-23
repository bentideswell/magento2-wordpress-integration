<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme\Deployment;

use FishPig\WordPress\App\Exception;
use FishPig\WordPress\App\Theme;
use FishPig\WordPress\App\Theme\RemoteHashProvider;
use FishPig\WordPress\App\Theme\DeploymentInterface;

class LocalCopy implements \FishPig\WordPress\App\Theme\DeploymentInterface
{
    /**
     *
     */
    private $appMode = null;

    /**
     *
     */
    private $theme= null;

    /**
     *
     */
    private $themeBuilder = null;

    /**
     *
     */
    private $wpDirectoryList = null;

    /**
     *
     */
    private $wpUrl = null;

    /**
     *
     */
    private $requestManager = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Theme\Builder $themeBuilder,
        \FishPig\WordPress\App\DirectoryList $wpDirectoryList,
        \FishPig\WordPress\App\Url $wpUrl,
        \FishPig\WordPress\App\HTTP\RequestManager $requestManager
    ) {
        $this->appMode = $appMode;
        $this->theme = $theme;
        $this->themeBuilder = $themeBuilder;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->wpUrl = $wpUrl;
        $this->requestManager = $requestManager;
    }

    /**
     *
     */
    public function isEnabled(): bool
    {
        return $this->appMode->isLocalMode();
    }

    /**
     *
     */
    public function deploy(): void
    {
        $blob = $this->themeBuilder->getBlob();
        $localHash = $this->theme->getLocalHash();

        if (!class_exists(\ZipArchive::class)) {
            throw new Exception(
                'Class ZipArchive does not exist but is required for automatic local theme installation.'
            );
        }

        $wpThemesPath = $this->getThemesPath();

        // Backup existing fishpig theme, if exists
        $existingFishPigTheme = $wpThemesPath . '/' . Theme::THEME_NAME;
        if (is_dir($existingFishPigTheme)) {
            $existingFishPigThemeBackup = $existingFishPigTheme . '-backup-'. time();

            if (!rename($existingFishPigTheme, $existingFishPigThemeBackup)) {
                throw new Exception(
                    sprintf(
                        'Unable to move FishPig theme from "%s" to "%s"',
                        $existingFishPigTheme,
                        $existingFishPigThemeBackup
                    )
                );
            }
        }

        // Write the ZIP file to disk
        $themeZipFile = $wpThemesPath . '/' . Theme::THEME_NAME . '-' . $localHash . '.zip';
        file_put_contents($themeZipFile, $blob);
        if (!is_file($themeZipFile)) {
            throw new Exception(
                sprintf(
                    'Unable to write file "%s" to disk.',
                    $themeZipFile
                )
            );
        }

        $zip = new \ZipArchive();

        if ($zip->open($themeZipFile) !== true) {
            throw new Exception(
                sprintf(
                    'Unable to open "%s" using ZipArchive',
                    $themeZipFile
                )
            );
        }

        $zip->extractTo($wpThemesPath);
        $zip->close();

        if (!is_dir($existingFishPigTheme)) {
            throw new Exception(
                sprintf(
                    'The theme was extracted but the theme cannot be found at "%s".',
                    $existingFishPigTheme
                )
            );
        }

        $remoteHashFile = $existingFishPigTheme . '/remote-hash.php';

        if (!is_file($remoteHashFile)) {
            throw new Exception(
                sprintf(
                    'Remote hash file "%s" is missing.',
                    $remoteHashFile
                )
            );
        }

        $remoteHash = include $remoteHashFile;

        if ($remoteHash !== $localHash) {
            throw new Exception(
                sprintf(
                    'Theme installed but new remote hash "%s" does not match the local hash "%s"',
                    $remoteHash,
                    $localHash
                )
            );
        }

        // Ensure that the theme is enabled
        if ($this->theme->getEnabledThemeName() !== Theme::THEME_NAME) {
            $this->theme->enable();
        }

        $client = $this->requestManager->get(
            $this->wpUrl->getSiteUrl('index.php?_fishpig=theme.update')
        );

        if ($localHash !== $this->theme->getRemoteHash()) {
            throw new Exception(
                sprintf(
                    'Local and remote hashes "%s" match, but the DB value "%s" is incorrect.',
                    $localHash,
                    $this->theme->getRemoteHash()
                )
            );
        }

        // Now lets clean thing sup
        unlink($themeZipFile);

        if (isset($existingFishPigThemeBackup)) {
            $this->removeDirectory($existingFishPigThemeBackup);
        }
    }

    /**
     *
     */
    private function getThemesPath(): string
    {
        if (!($wpBasePath = $this->wpDirectoryList->getBasePath())) {
            throw new Exception('Cannot get WP base path.');
        }

        $wpThemesPath = $wpBasePath . 'wp-content/themes';

        if (!is_dir($wpThemesPath) || !is_writable($wpThemesPath)) {
            throw new Exception(
                sprintf(
                    'Cannot write to themes folder "%s"',
                    $wpThemesPath
                )
            );
        }

        return $wpThemesPath;
    }

    /**
     *
     */
    private function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            foreach (scandir($dir) as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }

                $file = $dir . DIRECTORY_SEPARATOR . $item;

                if (is_file($file)) {
                    unlink($file);
                } elseif (is_dir($file)) {
                    $this->removeDirectory($file);
                }
            }

            rmdir($dir);
        }
    }
}
