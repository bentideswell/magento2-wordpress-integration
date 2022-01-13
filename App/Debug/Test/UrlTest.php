<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class UrlTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     * @param  \FishPig\WordPress\Model\UrlInterface $url
     */
    public function __construct(\FishPig\WordPress\Model\UrlInterface $url)
    {
        $this->url = $url;
    }

    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $this->url->doUrlsMatch(
            $this->url->getSiteUrl(),
            $this->url->getHomeUrl()
        );

        $this->url->getMagentoUrl();
        $this->url->getFront();
        $this->url->getHomeUrlWithFront();
        $this->url->getHomeUrlWithFront('test');
        $this->url->hasTrailingSlash();
        $this->url->getBlogRoute();
        $this->url->getCurrentUrl();
        $this->url->getCurrentUrl(true);
        $this->url->getWpContentUrl();
    }
}
