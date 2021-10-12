<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Url
{
    /**
     *
     */
    public function __construct(\FishPig\WordPress\App\Url $url)
    {
        $this->url = $url;
    }

    /**
     * Get the Magento base URL
     *
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
     * Generate a WordPress frontend URL
     *
     * @param  string $uri = ''
     * @return string
     */
    public function getUrl($uri = '')
    {
        return $this->url->getHomeUrl($uri);
    }

    /**
     * Generate a WordPress frontend URL with the Front var in it
     *
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
        echo __METHOD__ . '<br/>' . PHP_EOL;
        echo 'Move this to Image model';
        exit;
        $url = $this->optionManager->getOption('fileupload_url');

        if (!$url) {
            foreach (['upload_url_path', 'upload_path'] as $config) {
                if ($value = $this->optionManager->getOption($config)) {
                    if (strpos($value, 'http') === false) {
                        if (substr($value, 0, 1) !== '/') {
                            $url = $this->getSiteurl() . $value;
                        }
                    } else {
                        $url = $value;
                    }

                    break;
                }
            }

            if (!$url) {
                $url = rtrim($this->getWpContentUrl(), '/') . '/uploads/';
            }
        }

        return rtrim($url, '/') . '/';
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
