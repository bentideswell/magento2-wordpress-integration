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
     * @auto
     */
    protected $moduleDir = null;

    /**
     * @auto
     */
    protected $filesystem = null;

    /**
     * @auto
     */
    protected $modules = null;

    /**
     * @const string
     */
    const WPTHEME_DIR = 'wptheme';

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Module\Dir $moduleDir,
        \Magento\Framework\Filesystem $filesystem,
        array $modules = []
    ) {
        $this->moduleDir = $moduleDir;
        $this->filesystem = $filesystem;
        $this->modules = $modules;
    }

    /**
     * @return []
     */
    public function getFiles(): array
    {
        $files = [];

        foreach ($this->modules as $module) {
            $wpThemeDir = $this->filesystem->getDirectoryReadByPath(
                $this->moduleDir->getDir($module, '') . '/' . self::WPTHEME_DIR,
                \Magento\Framework\Filesystem\DriverPool::FILE
            );

            if ($wpThemeDir->isDirectory()) {
                foreach ($wpThemeDir->readRecursively() as $file) {
                    if ($wpThemeDir->isFile($file)) {
                        $files[str_replace('.php.sample', '.php', $file)] = $wpThemeDir->getAbsolutePath($file);
                    }
                }
            }
        }

        return $files;
    }
}
