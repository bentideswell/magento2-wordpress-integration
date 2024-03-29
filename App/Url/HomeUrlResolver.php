<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Url;

class HomeUrlResolver implements \FishPig\WordPress\Api\App\Url\UrlInterface
{
    /**
     * @auto
     */
    protected $option = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Model\OptionRepository $option
    ) {
        $this->option = $option;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->option->get('home');
    }
}
