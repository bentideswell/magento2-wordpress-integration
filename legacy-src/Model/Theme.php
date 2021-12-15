<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

class Theme
{
    /**
     * @return $this
     */
    public function validate()
    {
        return $this;
    }


    /**
     * @return bool
     */
    public function isActive()
    {


    }

    /**
     * @return bool
     */
    public function isThemeIntegrated()
    {
        echo __METHOD__;
        exit;
        return (int)$this->scopeConfig->getValue(
            'wordpress/setup/theme_integration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            (int)$this->storeManager->getStore()->getId()
        ) === 1;
    }
}
