<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\View;

use FishPig\WordPress\Api\App\View\AssetProviderInterface;

class AssetProvider implements AssetProviderInterface
{
    /**
     * @var []
     */
    private $assetProviderPool = [];
    
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        array $assetProviders = []
    ) {
        $this->appMode = $appMode;
        
        if ($this->appMode->isDisabled()) {
            return;
        }

        foreach ($assetProviders as $assetProviderId => $assetProvider) {
            if ($assetProvider instanceof AssetProviderInterface) {
                $this->assetProviderPool[$assetProviderId] = $assetProvider;
            } else {
                throw new \Magento\Framework\Exception\InvalidArgumentException(
                    __(
                        '%1 does not implement %2.',
                        get_class($assetProvider),
                        AssetProviderInterface::class
                    )
                );
            }
        }
    }
    
    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     * @param  \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function provideAssets(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ): void {
        if ($this->appMode->isDisabled()) {
            return;
        }

        foreach ($this->assetProviderPool as $assetProvider) {
            $assetProvider->provideAssets($request, $response);
        }
    }
}
