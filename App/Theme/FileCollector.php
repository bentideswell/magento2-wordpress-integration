<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

class FileCollector
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Module\Dir $moduleDir,
        \Magento\Framework\Filesystem\DriverInterface $filesystemDriver,
        array $modules = []
    ) {
        $this->moduleDir = $moduleDir;
        $this->filesystemDriver = $filesystemDriver;
        $this->modules = $modules;
    }

    /**
     * @return []
     */
    public function getFiles(): array
    {
        $files = [];

        foreach ($this->modules as $module) {
            $moduleEtcDir = $this->moduleDir->getDir($module, \Magento\Framework\Module\Dir::MODULE_ETC_DIR);
            $moduleDir = $this->filesystemDriver->getParentDirectory($moduleEtcDir);
            $moduleWpThemeDir = $moduleDir . '/' . $this->getTargetDir();

            if ($this->filesystemDriver->isDirectory($moduleWpThemeDir)) {
                $files[] = $this->collectFiles($moduleWpThemeDir);
            }
        }

        return $files ? array_merge(...$files) : [];
    }

    /**
     * @param  string $dir
     * @return []
     */
    private function collectFiles($dir, $baseDir = null): array
    {
        if (!$baseDir) {
            $baseDir = $dir;
        }

        $themeFiles = [];

        if (!$this->filesystemDriver->isDirectory($dir)) {
            return $themeFiles;
        }

        if ($files = $this->filesystemDriver->readDirectory($dir)) {
            foreach ($files as $file) {
                if ($this->filesystemDriver->isFile($file)) {
                    $rel = str_replace($baseDir . '/', '', $file);
                    $themeFiles[] = [$rel => $file];
                } elseif ($this->filesystemDriver->isDirectory($file)) {
                    $themeFiles[] = $this->collectFiles($file, $baseDir);
                }
            }
        }
        
        return $themeFiles ? array_merge(...$themeFiles) : [];
    }

    /**
     * @return string
     */
    public function getTargetDir(): string
    {
        return 'wptheme';
    }
}
