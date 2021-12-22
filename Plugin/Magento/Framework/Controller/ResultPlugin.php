<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Plugin\Magento\Framework\Controller;

class ResultPlugin
{
    /**
     * @param \FishPig\WordPress\App\View\AssetProvider $assetProvider
     */
    public function __construct(
        \FishPig\WordPress\App\View\AssetProvider $assetProvider,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->assetProvider = $assetProvider;
        $this->request = $request;
    }

    /**
     * @param  \Magento\Framework\Controller\ResultInterface $subject,
     * @param  \Magento\Framework\Controller\ResultInterface $result,
     * @param  \Magento\Framework\App\Response\Http $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function afterRenderResult(
        \Magento\Framework\Controller\ResultInterface $subject,
        \Magento\Framework\Controller\ResultInterface $result,
        \Magento\Framework\App\Response\Http $response
    ): \Magento\Framework\Controller\ResultInterface {
        $this->assetProvider->provideAssets(
            $this->request,
            $response
        );

        return $result;
    }
}
