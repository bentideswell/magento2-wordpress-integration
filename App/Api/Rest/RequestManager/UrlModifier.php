<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Api\Rest\RequestManager;

class UrlModifier implements \FishPig\WordPress\App\HTTP\RequestManager\UrlModifierInterface
{
    /**
     * @var \FishPig\WordPress\Model\UrlInterface
     */
    private $url = null;

    /**
     * @param \FishPig\WordPress\Model\UrlInterface $url
     */
    public function __construct(
        \FishPig\WordPress\Model\UrlInterface $url
    ) {
        $this->url = $url;
    }

    /**
     * @param  string $url
     * @return ?string
     */
    public function modifyUrl(string $url = null): ?string
    {
        if ($url === null) {
            throw new \FishPig\WordPress\App\Exception('Invalid URL given.');
        }

        return $this->url->getRestUrl($url);
    }
}
