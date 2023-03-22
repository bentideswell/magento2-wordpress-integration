<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use FishPig\WordPress\App\Theme;
use Magento\Framework\Exception\LocalizedException;

class PackageBuilder
{
    /**
     * @auto
     */
    protected $localHashProvider = null;

    /**
     * @auto
     */
    protected $fileCollector = null;

    /**
     * @const string
     */
    const TOKEN_REMOTE_HASH = '{REMOTE_HASH}';

    /**
     * @var string
     */
    private $buildDirectory = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\LocalHashProvider $localHashProvider,
        \FishPig\WordPress\App\Theme\FileCollector $fileCollector
    ) {
        $this->localHashProvider = $localHashProvider;
        $this->fileCollector = $fileCollector;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->build();
    }

    /**
     * @return string
     */
    private function build(): string
    {
        $packageFilename = Theme::THEME_NAME . '-wp-theme-' . substr($this->localHashProvider->getHash(), 0, 12) . '.zip';
        $absolutePackageFile = $this->getBuildDirectory() . '/' . $packageFilename;

        if (!is_file($absolutePackageFile)) {
            $files = $this->fileCollector->getFiles();

            if (class_exists(\ZipArchive::class)) {
                $this->buildUsingZipArchive($absolutePackageFile, $files);

                if (!is_file($absolutePackageFile)) {
                    throw new \FishPig\WordPress\App\Exception(
                        'Zip package was built using ZipArchive but the file does not exist.'
                    );
                }
            } else {
                throw new \FishPig\WordPress\App\Exception('\ZipArchive not installed. Cannot build WP theme zip file.');
            }
        }

        return $absolutePackageFile;
    }

    /**
     * @param  string $file
     * @param  array $files
     * @return void
     */
    private function buildUsingZipArchive($zipFile, array $files): void
    {
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE) !== true) {
            throw new \FishPig\WordPress\App\Exception('Unable to open Zip for writing at ' . $zipFile);
        }

        $localHash = $this->localHashProvider->getHash();

        foreach ($files as $relative => $file) {
            $relative = Theme::THEME_NAME . '/' . $relative;
            // phpcs:ignore -- file_get_contents
            $data = file_get_contents($file);

            if (strpos($data, self::TOKEN_REMOTE_HASH) !== false) {
                $zip->addFromString(
                    $relative,
                    str_replace(self::TOKEN_REMOTE_HASH, $localHash, $data)
                );
            } else {
                $zip->addFile($file, $relative);
            }
        }

        $zip->close();
    }

    /**
     *
     */
    private function getBuildDirectory(): string
    {
        if ($this->buildDirectory === null) {
            $varDirectory = BP . DIRECTORY_SEPARATOR . 'var';

            if (!is_dir($varDirectory)) {
                throw new LocalizedException(
                    __('Var directory does not exist. Cannot generate WP theme build.')
                );
            }

            $buildDirectory = $varDirectory . DIRECTORY_SEPARATOR . 'fishpig' . DIRECTORY_SEPARATOR . 'wptheme-builds';

            if (!is_dir($buildDirectory)) {
                mkdir($buildDirectory, 0755, true);

                if (!is_dir($buildDirectory)) {
                    throw new LocalizedException(
                        __(
                            'Unable to create WP build directory at %1.',
                            $buildDirectory
                        )
                    );
                }
            }

            $this->buildDirectory = $buildDirectory;
        }

        return $this->buildDirectory;
    }
}
