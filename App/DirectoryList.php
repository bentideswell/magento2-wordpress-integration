<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class DirectoryList
{
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
        \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
    ) {
        $this->pathResolver = $pathResolver;
        $this->storeManager = $storeManager;
        $this->filesystemDriver = $filesystemDriver;
    }
    
    /**
     * @return string|false
     */
    public function getBasePath()
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->basePath[$storeId])) {
            $this->basePath[$storeId] = false;
            
            // Get path or default to wp
            $path = $this->pathResolver->resolve()->getPath($storeId) ?: 'wp';

            if (substr($path, 0, 1) !== '/') {
                if ($this->filesystemDriver->isDirectory(BP . '/pub/' . $path)) {
                    $path = BP . '/pub/' . $path;
                } elseif ($this->filesystemDriver->isDirectory(BP . '/' . $path)) {
                    $path = BP . '/' . $path;
                }
            }

            if ($this->filesystemDriver->isDirectory($path)
                && $this->filesystemDriver->isFile($path . '/wp-config.php')) {
                $this->basePath[$storeId] = $path;
            }
        }

        return $this->basePath[$storeId];
    }

    /**
     * @return bool
     */
    public function isBasePathValid(): bool
    {
        return $this->getBasePath() !== false;
    }
}
