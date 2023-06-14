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
    private $theme = null;

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
    private $remoteHashProvider = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Theme $theme,
        \FishPig\WordPress\App\Theme\Builder $themeBuilder,
        \FishPig\WordPress\App\DirectoryList $wpDirectoryList,
        RemoteHashProvider $remoteHashProvider
    ) {
        $this->appMode = $appMode;
        $this->theme = $theme;
        $this->themeBuilder = $themeBuilder;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->remoteHashProvider = $remoteHashProvider;
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

        // Remove existing theme if exists
        $existingFishPigTheme = $wpThemesPath . '/' . Theme::THEME_NAME;

        if (is_dir($existingFishPigTheme)) {
            $this->removeDirectory($existingFishPigTheme);
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

        // Extract the ZIP file
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

        unlink($themeZipFile);

        // Check that the ZIP has extracted and the theme folder now exists
        if (!is_dir($existingFishPigTheme)) {
            throw new Exception(
                sprintf(
                    'The theme was extracted but the theme cannot be found at "%s".',
                    $existingFishPigTheme
                )
            );
        }

        // Check the remote hash in the theme
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
                    'Hash comparison failed using the include method. External remote hash is "%s" but the local hash is "%s". This can sometimes be fixed by flushing the cache.',
                    $remoteHash,
                    $localHash
                )
            );
        }

        // Update the remote hash in the DB
        $this->remoteHashProvider->update($remoteHash);

        // And check that the DB remote hash is correct
        if ($localHash !== $this->theme->getRemoteHash()) {
            throw new Exception(
                sprintf(
                    'Local and remote hashes "%s" match, but the DB value "%s" is incorrect.',
                    $localHash,
                    $this->theme->getRemoteHash()
                )
            );
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
                    @unlink($file);
                } elseif (is_dir($file)) {
                    $this->removeDirectory($file);
                }
            }

            @rmdir($dir);
        }
    }
}
