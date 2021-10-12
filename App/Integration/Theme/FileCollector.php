<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Theme;

class FileCollector
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Module\Dir $moduleDir,
        array $modules = []
    ) {
        $this->moduleDir = $moduleDir;
        $this->modules = $modules;
    }

    /**
     * @return []
     */
    public function getFiles(): array
    {
        $files = [];

        foreach ($this->modules as $module) {
            $moduleDir = dirname($this->moduleDir->getDir($module, \Magento\Framework\Module\Dir::MODULE_ETC_DIR))
                 . '/' . $this->getTargetDir();

            if (is_dir($moduleDir)) {
                $files = array_merge($files, $this->collectFiles($moduleDir));
            }
        }

        return $files;
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

        if (is_dir($dir) && ($files = scandir($dir))) {
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || $file === '.git' || $file === 'README.md') {
                    continue;
                }

                $tmp = $dir . '/' . $file;
                $rel = str_replace($baseDir . '/', '', $tmp);

                if (is_file($tmp)) {
                    $themeFiles[$rel] = $tmp;
                } elseif (is_dir($tmp)) {
                    $themeFiles = array_merge($themeFiles, $this->collectFiles($tmp, $baseDir));
                }
            }
        }

        return $themeFiles;
    }

    /**
     * @return string
     */
    public function getTargetDir(): string
    {
        return 'wptheme';
    }
}
