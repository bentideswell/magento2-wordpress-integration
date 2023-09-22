<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\View\AssetProvider;

use FishPig\WordPress\Api\App\View\AssetProviderInterface;

class CleanUp implements AssetProviderInterface
{
    /**
     * @const string
     */
    const RP_SHORTCODE = 'related_products';

    /**
     * @param  \Magento\Framework\App\RequestInterface $request
     * @param  \Magento\Framework\App\ResponseInterface $response
     * @return void
     */
    public function provideAssets(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\ResponseInterface $response
    ): void {
        // Clean up any Related Products shortcodes. These can be left in the content
        // after uninstalling the RP module, when not using PSW.
        $response->setBody(
            preg_replace(
                '/\[' . self::RP_SHORTCODE . '[^\]]*\]/U',
                '',
                $response->getBody()
            )
        );
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

        $html = $response->getBody();

        if (strpos($html, '[' . self::RP_SHORTCODE) !== false) {
            return true;
        }

        return false;
    }
}
