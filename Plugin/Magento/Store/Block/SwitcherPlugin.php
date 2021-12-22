<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Magento\Store\Block;

class SwitcherPlugin
{
    /**
     * @var \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface
     */
    private $storeSwitcherUrlProvider = null;

    /**
     * @param \Magento\Framework\Url\EncoderInterface $encoder
     * @param \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface $storeSwitcherUrlProvider = null
     */
    public function __construct(
        \Magento\Framework\Url\EncoderInterface $encoder,
        \FishPig\WordPress\Api\Data\StoreSwitcherUrlProviderInterface $storeSwitcherUrlProvider = null
    ) {
        $this->encoder = $encoder;
        $this->storeSwitcherUrlProvider = $storeSwitcherUrlProvider;
    }

    /**
     *
     */
    public function aroundGetTargetStorePostData(
        \Magento\Store\Block\Switcher $subject,
        \Closure $callback,
        \Magento\Store\Model\Store $store,
        $data = []
    ) {
        $originalResult = $callback($store, $data);
     
        if ($this->storeSwitcherUrlProvider === null) {
            return $originalResult;
        }

        $redirectUrl = $this->storeSwitcherUrlProvider->getUrl($store);
        
        if (!$redirectUrl) {
            return $originalResult;
        }

        $json = json_decode($originalResult, true);

        if (isset($json['data']['uenc'])) {
            $json['data']['uenc'] = $this->encoder->encode($redirectUrl);
            $json = json_encode($json);
        }

        return $json;
    }
}
