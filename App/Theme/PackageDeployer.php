<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class PackageDeployer
{
    /**
     * @param \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
     */
    public function __construct(\Magento\Framework\Filesystem\DriverInterface $filesystemDriver)
    {
        $this->filesystemDriver = $filesystemDriver;
    }
    
    /**
     * @return void
     */
    public function deploy(string $packageFile, string $wpPath): void
    {
        if (!$this->filesystemDriver->isFile($packageFile)) {
            throw new \FishPig\WordPress\App\Exception($packageFile . ' does not exist.');
        }
        
        if (!$this->filesystemDriver->isDirectory($wpPath)) {
            throw new \FishPig\WordPress\App\Exception(
                'WordPress path (' . $wpPath . ') is invalid.'
            );
        }

        $wpThemePath = $wpPath . '/wp-content/themes';
        
        if (!$this->filesystemDriver->isDirectory($wpThemePath)) {
            throw new \FishPig\WordPress\App\Exception('Unable to find ' . $wpThemePath);
        }
        
        $fishPigThemePath = $wpThemePath . '/fishpig';
        
        if ($this->filesystemDriver->isDirectory($fishPigThemePath)) {
            $tempFishPigThemePath = $fishPigThemePath . date('-YmdHis-') . rand(100, 999) . '.delete';
            
            $this->filesystemDriver->rename($fishPigThemePath, $tempFishPigThemePath);
            
            if ($this->filesystemDriver->isDirectory($fishPigThemePath)) {
                throw new \FishPig\WordPress\App\Exception(
                    'Unable to remove existing FishPig theme from ' . $fishPigThemePath
                );
            }
        }
        
        // phpcs:ignore -- basename is OK!
        $migratedZipFile = $wpThemePath . '/' . basename($packageFile);

        $this->filesystemDriver->copy($packageFile, $migratedZipFile);

        $zip = new \ZipArchive;

        if ($zip->open($packageFile) !== true) {
            throw new \FishPig\WordPress\App\Exception('Unable to open ' . $packageFile . ' using ZipArchive.');
        }

        $zip->extractTo($wpThemePath);
        $zip->close();

        if (isset($tempFishPigThemePath)) {
            $this->recursiveDeleteDir($tempFishPigThemePath);
        }
    }

    /**
     * @param  string $baseDir
     * @return void
     */
    private function recursiveDeleteDir(string $baseDir, $level = 0): void
    {
        if ($this->filesystemDriver->isDirectory($baseDir)) {
            if ($files = $this->filesystemDriver->readDirectory($baseDir)) {
                foreach ($files as $file) {
                    if ($this->filesystemDriver->isFile($file)) {
                        $this->filesystemDriver->deleteFile($file);
                    } elseif ($this->filesystemDriver->isDirectory($file)) {
                        $this->recursiveDeleteDir($file, $level+1);
                        $this->filesystemDriver->deleteDirectory($file);
                    }
                }
            }

            if ($level === 0) {
                $this->filesystemDriver->deleteDirectory($baseDir);
            }
        }
    }
}
