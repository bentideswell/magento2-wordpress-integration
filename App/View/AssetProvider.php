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
     * @var bool
     */
    private $canProvideAssets = null;

    /**
     *
     */
    private $appMode = null;

    /**
     *
     */
    private $integrationTests = null;

    /**
     * All asset providers
     */
    private $assetProviderPool = [];


    /**
     *
     */
    private $activeAssetProviderIndexes = null;

    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Integration\Tests\Proxy $integrationTests,
        array $assetProviders = []
    ) {
        $this->appMode = $appMode;
        $this->integrationTests = $integrationTests;

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

        if (count($this->assetProviderPool) === 0) {
            return;
        }

        if (!$this->canProvideAssets($request, $response)) {
            return;
        }

        // Filter asset providers to only include providers that can run
        // for current request context
        $assetProviders = [];

        foreach ($this->assetProviderPool as $assetProvider) {
            if ($assetProvider->canProvideAssets($request, $response)) {
                $assetProviders[] = $assetProvider;
            }
        }

        if (count($assetProviders) === 0) {
            // We asked all of the asset providers but none can provide assets
            // for the current context
            return;
        }

        try {
            if ($this->integrationTests->runTests() === false) {
                return;
            }
        } catch (\FishPig\WordPress\App\Exception  $e) {
            return;
        }

        // Run the provideAssets methods against our filtered
        // asset provider list
        foreach ($assetProviders as $assetProvider) {
            $assetProvider->provideAssets($request, $response);
        }
    }

    /**
     * This does some generic and early testing to see whether assets can be provided.
     * If this returns false then we won't even ask the asset providers.
     *
     * @return bool
     */
    public function canProvideAssets(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ): bool {
        if (!$request->isGet()) {
            return false;
        }

        if (!in_array($response->getHttpResponseCode(), [200, 404])) {
            return false;
        }

        if (in_array($request->getModuleName(), $this->getDisallowedModuleNames())) {
            return false;
        }

        if (!$response->getBody()) {
            return false;
        }

        return true;
    }

    /**
     *
     */
    public function getDisallowedModuleNames(): array
    {
        return ['api'];
    }
}
