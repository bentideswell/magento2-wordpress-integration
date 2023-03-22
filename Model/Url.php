<?php
/**
 * @deprecated 3.0.0
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Url
{
    /**
     * @auto
     */
    protected $url = null;

    /**
     * @param \FishPig\WordPress\App\Url $url
     */
    public function __construct(\FishPig\WordPress\App\Url $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMagentoUrl()
    {
        return $this->url->getMagentoUrl();
    }

    /**
     * @return string
     */
    public function getBlogRoute()
    {
        return $this->url->getBlogRoute();
    }

    /**
     * @return string
     */
    public function getUrl($uri = '')
    {
        return $this->url->getHomeUrl($uri);
    }

    /**
     * @param  string $uri = ''
     * @return string
     */
    public function getUrlWithFront($uri = '')
    {
        return $this->url->getUrlWithFront($uri);
    }

    /**
     * @return string
     */
    public function getSiteurl($uri = '')
    {
        return $this->url->getSiteUrl($uri);
    }

    /**
     * @return string
     */
    public function getHomeUrl()
    {
        return $this->url->getHomeUrl();
    }

    /**
     * @return
     */
    public function getWpContentUrl()
    {
        return $this->url->getWpContentUrl();
    }

    /**
     * Retrieve the upload URL
     *
     * @return string
     */
    public function getFileUploadUrl()
    {
        return '';
    }

    /**
     * @return bool
     */
    public function isRoot()
    {
        return $this->url->isRoot();
    }

    /**
     * @return string
     */
    public function getFront()
    {
        return $this->url->getFront();
    }
}
