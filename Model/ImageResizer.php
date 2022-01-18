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
     * @const string
     */
    const RESIZE_MEDIA_DIR = 'wordpress';

    /**
     * @var
     */
    private $adapter;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * @var
     */
    private static $targetDirectory = null;

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
        \Magento\Framework\Filesystem\Directory\WriteFactory $writeFactory
    ) {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->url = $url;
        $this->wpDirectoryList = $wpDirectoryList;
        $this->writeFactory = $writeFactory;
        $appMode->requireLocalMode();
    }

    /**
     * @param  \FishPig\WordPress\Model\Image $image
     * @return self
     */
    public function setImage(\FishPig\WordPress\Model\Image $image): self
    {
        // Reset args
        $this->args = [];
        $this->adapter = false;

        if (!$this->wpDirectoryList->isBasePathValid()) {
            throw new \FishPig\WordPress\App\Exception('Invalid base path. Unable to resize image.');
        }

        $siteUrl = $this->stripProtocolFromUrl($this->url->getSiteUrl());
        $wpRelativeFile = ltrim(str_replace($siteUrl, '', $this->stripProtocolFromUrl($image->getGuid())), '/');
        $wpDir = $this->wpDirectoryList->getBaseDirectory();

        if (!$wpDir->isFile($wpRelativeFile)) {
            throw \FishPig\WordPress\Model\Image\NoSuchSourceFileException::withFile(
                $wpRelativeFile
            );
        }

        $image = $wpDir->getAbsolutePath($wpRelativeFile);

        $this->adapter = $this->imageFactory->create();
        $this->adapter->open($image);
        $this->args['original_file'] = $image;

        return $this;
    }

    /**
     * @param  string $url
     * @return string
     */
    private function stripProtocolFromUrl(string $url): string
    {
        return str_replace(['https://', 'http://'], '', $url);
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

        $targetDirectory = $this->getTargetDirectoryWrite();

        // phpcs:ignore -- not cryptographic
        $targetFile = $targetDirectory->getAbsolutePath() . md5(http_build_query($this->args)) . $this->getFormat();

        if (!$targetDirectory->isFile($targetFile)) {
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
     * @return \Magento\Framework\Filesystem\Directory\Write
     */
    protected function getTargetDirectoryWrite()
    {
        if (self::$targetDirectory === null) {
            $mediaDir = $this->filesystem->getDirectoryWrite(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            );
            
            if (!$mediaDir->isDirectory(self::RESIZE_MEDIA_DIR)) {
                $mediaDir->create(self::RESIZE_MEDIA_DIR);
            }
            
            if (!$mediaDir->isDirectory(self::RESIZE_MEDIA_DIR)) {
                throw new \Exception(
                    'Unable to create directory for resized blog image.'
                );
            }

            self::$targetDirectory = $this->writeFactory->create(
                $mediaDir->getAbsolutePath(self::RESIZE_MEDIA_DIR),
                \Magento\Framework\Filesystem\DriverPool::FILE
            );
        }
        
        return self::$targetDirectory;
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
