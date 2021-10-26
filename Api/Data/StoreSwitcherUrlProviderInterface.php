<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Api\Data;

interface StoreSwitcherUrlProviderInterface
{
    /**
     * @param  \Magento\Store\Model\Store $store
     * @return false|string
     */
    public function getUrl(\Magento\Store\Model\Store $store);
}
