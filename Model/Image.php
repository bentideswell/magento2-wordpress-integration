<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Image extends \FishPig\WordPress\Model\Post\Attachment
{
    /**
     * @param  string $code
     * @return string
     */
    public function getImageUrl(string $code): string
    {
        if ($imageFile = $this->getImageByCode($code, 'file')) {
            return $this->url->getUploadUrl() . dirname($this->getFile()) . '/' . $imageFile;
        }
        
        return '';
    }
    
    /**
     * @return array
     */
    public function getSizes(): array
    {
        return $this->getData('sizes') ?? [];
    }

    /**
     * @param  string $code
     * @param  string|null $field = null
     * @return mixed
     */
    private function getImageByCode(string $code, $field = null)
    {
        $sizes = $this->getSizes();
        
        if (!isset($sizes[$code])) {
            return false;
        }
        
        if ($field === null) {
            return $sizes[$code];
        }
        
        return $sizes[$code][$field];
    }
    
    /**
     * This allows you to access image URLs using nice methods:
     * $this->getMediumUrl() calls $this->getImageUrl('medium')
     */
    public function __call($method, $args)
    {
        if (strlen($method) > 6) {
            if (strpos($method, 'get') === 0) {
                if (substr($method, -3) === 'Url') {
                    $imageCode = $this->_underscore(substr($method, 3, -3));
                } elseif (substr($method, -5) === 'Image') {
                    $imageCode = $this->_underscore(substr($method, 3, -5));   
                }
                
                if (!empty($imageCode)) {
                    $sizes = $this->getSizes();
            
                    if (isset($sizes[$imageCode])) {
                        return $this->getImageUrl($imageCode);
                    } else {
                        $imageCode = str_replace('_', '-', $imageCode);

                        if (isset($sizes[$imageCode])) {
                            return $this->getImageUrl($imageCode);
                        }
                    }
                }
            }
        }
        
        return parent::__call($method, $args);
    }
    
    /**
     * @return string
     */
    public function getFullSizeImage(): string
    {
        return $this->getData('guid');
    }

    /**
     * @return string
     */
    public function getAvailableImage(): string
    {
        if ($sizes = $this->getSizes()) {
            foreach ($sizes as $type => $data) {
                return $this->getImageUrl($type);
            }
        }

        return '';
    }

    /**
     * Retrieve the alt text for the image
     *
     * @return string
     */
    public function getAltText()
    {
        return $this->getMetaValue('image_alt');
    }

    /**
     * Retrieve the description for the image
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->_getData('post_content');
    }

    /**
     * Retrieve the title for the image
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_getData('post_title');
    }

    /**
     * Retrieve the caption for the image
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->_getData('post_excerpt');
    }
    
    /**
     * @deprecated since 2.0
     * @param  string $type = 'thumbnail'
     * @return string
     */
    public function getImageByType($type = 'thumbnail')
    {
        return $this->getImageUrl($type);
    }
}
