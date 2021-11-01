<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Request\AssetProvider;

use FishPig\WordPress\Api\App\Request\AssetProviderInterface;

class AssetProviderPool
{
    /**
     * @var []
     */
    private $assetProviders = [];

    /**
     * @param array $assetProviders = []
     */
    public function __construct(array $assetProviders = [])
    {
        foreach ($assetProviders as $assetProviderId => $assetProvider) {
            if ($assetProvider instanceof AssetProviderInterface) {
                $this->assetProviders[$assetProviderId] = $assetProvider;
            }
        }
    }

    /**
     * @return array
     */
    public function getAssetProviders(): array
    {
        return $this->assetProviders;
    }
}
