<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Request;

class AssetProvider implements \FishPig\WordPress\Api\App\Request\AssetProviderInterface
{
    /**
     * @var []
     */
    private $assetProviderPool = [];
    
    /**
     *
     */
    public function __construct(
        array $assetProviders = []
    ) {
        foreach ($assetProviders as $assetProviderId => $assetProvider) {
            if ($assetProvider instanceof \FishPig\WordPress\Api\App\Request\AssetProviderInterface) {
                $this->assetProviderPool[$assetProviderId] = $assetProvider;
            }
        }
    }
    
    /**
     * @param  \Magento\Framework\App\Response\Http $response
     * @return void
     */
    public function provideAssets(\Magento\Framework\App\Response\Http $response): void
    {
        foreach ($this->assetProviderPool as $assetProvider) {
            $assetProvider->provideAssets($response);
        }
    }
}
