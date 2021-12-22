<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use \Magento\Framework\App\Filesystem\DirectoryList;

class PackageBuilder
{
    /**
     * @const string
     */
    const TOKEN_REMOTE_HASH = '{REMOTE_HASH}';

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\LocalHashProvider $localHashProvider,
        \FishPig\WordPress\App\Theme\FileCollector $fileCollector,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
    ) {
        $this->localHashProvider = $localHashProvider;
        $this->fileCollector = $fileCollector;
        $this->directoryList = $directoryList;
        $this->filesystemDriver = $filesystemDriver;
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
        $file = $this->directoryList->getPath(DirectoryList::MEDIA)
            . '/fishpig-wp-theme-' . substr($this->localHashProvider->getHash(), 0, 12) . '.zip';

        if ($this->filesystemDriver->isFile($file)) {
            return $file;
        }

        $files = $this->fileCollector->getFiles();

        if (class_exists(\ZipArchive::class)) {
            $this->buildUsingZipArchive($file, $files);
        } else {
            throw new \FishPig\WordPress\App\Exception('Unable to build zip file without ZipArchive.');
        }

        return $file;
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
            $relative = 'fishpig/' . $relative;
            $data = $this->filesystemDriver->fileGetContents($file);
            
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

        if (!$this->filesystemDriver->isFile($zipFile)) {
            throw new \FishPig\WordPress\App\Exception('Failed to create ' . $zipFile . ' using ZipArchive');
        }
    }
}
