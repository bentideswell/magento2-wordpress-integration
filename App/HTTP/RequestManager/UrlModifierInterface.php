<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\RequestManager;

interface UrlModifierInterface
{
    /**
     * @param  string $url = null
     * @return ?string
     */
    public function modifyUrl(string $url = null): ?string;
}
