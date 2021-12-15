<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Image;

class ImageResizer
{
    /**
     * @var \FishPig\WordPress\Model\Image
     */
    private $image = null;
    
    /**
     * @param  string|Image $image
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return '#';
    }

    /**
     * @param  int|null $width
     * @param  int|null $height
     * @return string
     */
    public function resize($width = null, $height = null)
    {
        return (string)$this;
    }

    /**
     * Get/set keepAspectRatio
     *
     * @param  bool $value
     * @return bool
     */
    public function keepAspectRatio($value)
    {
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
        return $this;
    }
}
