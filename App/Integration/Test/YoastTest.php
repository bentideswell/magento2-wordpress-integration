<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;

class YoastTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @param \FishPig\WordPress\App\Option $option
     */
    public function __construct(
        \Magento\Framework\App\State $appState,
        \FishPig\WordPress\Model\PluginManager $pluginManager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->appState = $appState;
        $this->pluginManager = $pluginManager;
        $this->moduleManager = $moduleManager;
    }
    
    /**
     * @return void
     */
    public function runTest(): void
    {
        try {
            $isAdmin = $this->appState->getAreaCode() === 'adminhtml';
        } catch (\Exception $e) {
            $isAdmin = false;
        }
        
        if ($this->isYoastPluginInstalled() && $this->isYoastModuleInstalled()) {
            return;
        } elseif ($this->isYoastPluginInstalled()) {
            if ($isAdmin) {
                throw new IntegrationRecoverableException(
                    sprintf(
                        'The Yoast SEO plugin requires the free <a href="%s">Yoast Magento 2 module</a>.',
                        'https://fishpig.co.uk/magento/wordpress-integration/yoast/'
                    )
                );
            } else {
                throw new IntegrationRecoverableException(
                    sprintf(
                        'Install the Magento 2 Yoast module for integrated SEO data from WordPress. Download at %s',
                        'https://fishpig.co.uk/magento/wordpress-integration/yoast/'
                    )
                );
            }
        }
    }

    /**
     * @return bool
     */
    private function isYoastPluginInstalled(): bool
    {
        return $this->pluginManager->isEnabled('wordpress-seo/wp-seo.php')
            || $this->pluginManager->isEnabled('wordpress-seo-premium/wp-seo-premium.php');
    }
    
    /**
     * @return bool
     */
    private function isYoastModuleInstalled(): bool
    {
        return $this->moduleManager->isEnabled('FishPig_WordPress_Yoast');
    }
}
