<?php
/**
 *
 *
 *
 */
namespace FishPig\WordPress\Model;

use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\Image;

class ImageResizer
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var 
     */
    protected $adapter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var array
     */
    protected $args = [];

    /**
     *
     */
    public function __construct(Filesystem $filesystem, AdapterFactory $imageFactory, StoreManagerInterface $storeManager)
    {
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string|Image $image
     * @return $this
     */
    public function setImage($image)
    {  
        $this->args = [];

        if (is_object($image)) {
            $image = $image->getLocalFile();
        }  

        if (!$image) {
            throw new \Exception('Cannot create ' . __CLASS__ . ' as no image is set.');
        }

        $this->adapter = $this->imageFactory->create();

        $this->adapter->open($image);

        $this->args['original_file'] = $image;

        return $this;
    }

    /**
     * @param int|null $width
     * @param int|null $height
     * @return string
     */
    public function resize($width = null, $height = null)
    {
        $this->args['width'] = $width;
        $this->args['height'] = $height;

        $targetDirectory = $this->getTargetDirectory();

        if (!is_dir($targetDirectory)) {
            @mkdir($targetDirectory);

            if (!is_dir($targetDirectory)) {
                return false;
            }
        }

        $targetFile = $targetDirectory . md5(http_build_query($this->args)) . $this->getFormat();

        if (!is_file($targetFile)) {
            $this->adapter->resize($width, $height);
            $this->adapter->save($targetFile);
        }

        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'wordpress/' . basename($targetFile);
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
        return $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath() . 'wordpress' . DIRECTORY_SEPARATOR;
    }

    /**
     * Get/set keepAspectRatio
     *
     * @param bool $value
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
     * @param bool $value
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
     * @param bool $value
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
     * @param bool $value
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
     * @param null|array $value
     * @return array|null
     */
    public function backgroundColor($value)
    {
        $this->args['background_color'] = $value;

        $this->adapter->backgroundColor($value);

        return $this;
    }
}
