<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Image;

class ImageResizer
{
    /**
     * @var
     */
    private $adapter;

    /**
     * @var array
     */
    protected $args = [];

    /**
     *
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\Model\UrlInterface $url,
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\DirectoryList $wpDirectoryList,
        \Magento\Framework\Filesystem\DriverInterface $filesystemDriver
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->filesystemDriver = $filesystemDriver;
        $appMode->requireLocalMode();
    }

    /**
     * @param  string|Image $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->args = [];

        if (is_object($image)) {
            $image = $this->getLocalFile($image);
        }

        if (!$image || !$this->filesystemDriver->isFile($image)) {
            throw new \FishPig\WordPress\App\Exception(
                'Unable to resize image due to invalid or missing local image.'
            );
        }

        $this->adapter = $this->imageFactory->create();

        $this->adapter->open($image);

        $this->args['original_file'] = $image;

        return $this;
    }

    /**
     *
     */
    private function getLocalFile(\FishPig\WordPress\Model\Image $image)
    {
        if (!$this->wpDirectoryList->isBasePathValid()) {
            throw new \FishPig\WordPress\App\Exception('Invalid base path. Unable to resize image.');
        }
        
        $siteUrl = str_replace(['https://', 'http://'], '', $this->url->getSiteUrl());
        $guid = str_replace(['https://', 'http://'], '', $image->getData('guid'));
        $localFile = $this->wpDirectoryList->getBasePath() . '/' . ltrim(str_replace($siteUrl, '', $guid), '/');
        
        return $this->filesystemDriver->isFile($localFile) ? $localFile : false;
    }
    
    /**
     * @param  int|null $width
     * @param  int|null $height
     * @return string
     */
    public function resize($width = null, $height = null)
    {
        $this->args['width'] = $width;
        $this->args['height'] = $height;

        $targetDirectory = $this->getTargetDirectory();

        if (!$this->filesystemDriver->isDirectory($targetDirectory)) {
            $this->filesystemDriver->createDirectory($targetDirectory);
            if (!$this->filesystemDriver->isDirectory($targetDirectory)) {
                return false;
            }
        }

        // phpcs:ignore -- not cryptographic
        $targetFile = $targetDirectory . md5(http_build_query($this->args)) . $this->getFormat();

        if (!$this->filesystemDriver->isFile($targetFile)) {
            $this->adapter->resize($width, $height);
            $this->adapter->save($targetFile);
        }

        return $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        ) . 'wordpress/' . basename($targetFile); // phpcs:ignore -- basename is OK
    }

    /**
     * @return string
     */
    protected function getFormat()
    {
        return substr($this->args['original_file'], strrpos($this->args['original_file'], '.'));
    }

    /**
     * @return string
     */
    protected function getTargetDirectory()
    {
        return $this->filesystem->getDirectoryRead(
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
        )->getAbsolutePath() . 'wordpress' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get/set keepAspectRatio
     *
     * @param  bool $value
     * @return bool
     */
    public function keepAspectRatio($value)
    {
        $this->args['keep_aspect_ratio'] = $value;

        $this->adapter->keepAspectRatio($value);

        return $this;
    }

    /**
     * Get/set keepFrame
     *
     * @param  bool $value
     * @return bool
     */
    public function keepFrame($value)
    {
        $this->args['keep_frame'] = $value;

        $this->adapter->keepFrame($value);

        return $this;
    }

    /**
     * Get/set keepTransparency
     *
     * @param  bool $value
     * @return bool
     */
    public function keepTransparency($value)
    {
        $this->args['keep_transparency'] = $value;

        $this->adapter->keepTransparency($value);

        return $this;
    }

    /**
     * Get/set constrainOnly
     *
     * @param  bool $value
     * @return bool
     */
    public function constrainOnly($value)
    {
        $this->args['constrain_only'] = $value;

        $this->adapter->constrainOnly($value);

        return $this;
    }

    /**
     * Get/set backgroundColor
     *
     * @param  null|array $value
     * @return array|null
     */
    public function backgroundColor($value)
    {
        $this->args['background_color'] = $value;

        $this->adapter->backgroundColor($value);

        return $this;
    }
}
