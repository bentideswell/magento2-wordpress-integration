<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

use Magento\Framework\Filesystem\DriverPool;

class DirectoryList
{
    /**
     * @auto
     */
    protected $pathResolver = null;

    /**
     * @auto
     */
    protected $storeManager = null;

    /**
     * @auto
     */
    protected $filesystem = null;

    /**
     * @var array
     */
    private $basePath = [];

    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\DirectoryList\PathResolver $pathResolver,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->pathResolver = $pathResolver;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\Read|false
     */
    public function getBaseDirectory()
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->basePath[$storeId])) {
            $this->basePath[$storeId] = false;

            // Get path or default to wp
            $path = $this->pathResolver->resolve()->getPath($storeId) ?: 'wp';

            if (substr($path, 0, 1) !== '/') {
                $wpDir = $this->filesystem->getDirectoryReadByPath(
                    BP . '/pub/' . $path,
                    DriverPool::FILE
                );

                if (!$wpDir->isDirectory()) {
                    $wpDir = $this->filesystem->getDirectoryReadByPath(
                        BP . '/' . $path,
                        DriverPool::FILE
                    );
                }
            } else {
                $wpDir = $this->filesystem->getDirectoryReadByPath(
                    $path,
                    DriverPool::FILE
                );
            }

            if (isset($wpDir) && $wpDir->isDirectory()) {
                if ($wpDir->isFile('wp-config.php')) {
                    $this->basePath[$storeId] = $wpDir;
                }
            }
        }

        return $this->basePath[$storeId];
    }

    /**
     * @return bool
     */
    public function isBasePathValid(): bool
    {
        return $this->getBaseDirectory() !== false;
    }

    /**
     * @return string|false
     */
    public function getBasePath()
    {
        if ($wpDirectory = $this->getBaseDirectory()) {
            return $wpDirectory->getAbsolutePath();
        }

        return false;
    }
}
