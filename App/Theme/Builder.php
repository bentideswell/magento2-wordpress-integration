<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use FishPig\WordPress\App\Theme;

class Builder
{
    /**
     * @const string
     */
    const TOKEN_REMOTE_HASH = '{REMOTE_HASH}';

    /**
     *
     */
    const TAGS = '{TAGS}';

    /**
     *
     */
    private $localHashProvider = null;

    /**
     *
     */
    private $fileCollector = null;

    /**
     *
     */
    private $themeUrl = null;

    /**
     *
     */
    private $blob = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Theme\LocalHashProvider $localHashProvider,
        \FishPig\WordPress\App\Theme\FileCollector $fileCollector,
        \FishPig\WordPress\App\Theme\Url $themeUrl
    ) {
        $this->localHashProvider = $localHashProvider;
        $this->fileCollector = $fileCollector;
        $this->themeUrl = $themeUrl;
    }

    /**
     * @return string
     */
    public function getLocalFile(): string
    {
        $localFile = BP . '/var/wp-theme-' . $this->localHashProvider->getHash() . '.zip';

        if (!is_file($localFile)) {
            file_put_contents(
                $localFile,
                $this->getBlob()
            );
        }

        return $localFile;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->themeUrl->getUrl();
    }

    /**
     * @return string
     */
    public function getBlob()
    {
        if ($this->blob === null) {
            $this->blob = $this->getBlobUsingZipArchive();

            if (!$this->blob) {
                $this->blob = null;
                throw new \FishPig\WordPress\App\Exception(
                    'ZipArchive exists but theme blob was empty.'
                );
            }
        }

        return $this->blob;
    }


    /**
     * @param  string $file
     * @param  array $files
     * @return void
     */
    private function getBlobUsingZipArchive(): string
    {
        if (!class_exists(\ZipArchive::class)) {
            throw new \FishPig\WordPress\App\Exception(
                'Class ZipArchive not found. Cannot build theme.'
            );
        }

        if (!($files = $this->fileCollector->getFiles())) {
            throw new \FishPig\WordPress\App\Exception(
                'No files found for WP theme generation.'
            );
        }

        if (!($tempFile = tmpfile())) {
            throw new \FishPig\WordPress\App\Exception(
                'Unable to create temp file.'
            );
        }

        // Get the actual physical location of the temporary file
        $tempFileLocation = stream_get_meta_data($tempFile)['uri']; //"location" of temp file

        // Now lets build the ZIP
        $zip = new \ZipArchive();

        if ($zip->open($tempFileLocation, \ZipArchive::OVERWRITE) !== true) {
            throw new \FishPig\WordPress\App\Exception('Unable to open Zip for writing at ' . $zipFile);
        }

        $localHash = $this->localHashProvider->getHash();

        foreach ($files as $relative => $file) {
            $relative = Theme::THEME_NAME . '/' . $relative;
            // phpcs:ignore -- file_get_contents
            $data = file_get_contents($file);

            if (strpos($data, self::TAGS) !== false) {
                $data = str_replace(
                    self::TAGS,
                    $this->getTags(),
                    $data
                );
            }

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

        return file_get_contents($tempFileLocation);
    }

    /**
     *
     */
    private function getTags(): ?string
    {
        if ($tags = $this->fileCollector->getTags()) {
            return implode(', ', $tags);
        }

        return 'fishpig, magento';
    }
}
