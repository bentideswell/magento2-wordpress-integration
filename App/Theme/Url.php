<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Theme;

use FishPig\WordPress\App\Theme;

class Url
{
    /**
     * @const string
     */
    const PATH_INFO = 'wordpress/theme/latest.zip';

    /**
     *
     */
    const FILENAME = 'fishpig.zip';

    /**
     *
     */
    private $urlBuilder = null;

    /**
     *
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->urlBuilder->getUrl(
            '',
            [
                '_direct' => self::PATH_INFO
            ]
        );
    }
}
