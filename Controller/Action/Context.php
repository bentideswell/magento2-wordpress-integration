<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Action;

use Magento\Framework\Registry;
use FishPig\WordPress\Model\UrlInterface;

class Context
{
    /**
     * @param Registry $registry
     * @param Url      $url
     */
    public function __construct(
        Registry $registry,
        UrlInterface $url
    ) {
        $this->registry = $registry;
        $this->url = $url;
    }

    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
    }

    /**
     * @return UrlInterface
     */
    public function getUrl(): UrlInterface
    {
        return $this->url;
    }
}
