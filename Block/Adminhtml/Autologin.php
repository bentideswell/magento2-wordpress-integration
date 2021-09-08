<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Adminhtml;

class Autologin extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function getExtensionUrl(): string
    {
        return 'https://fishpig.co.uk/magento/wordpress-integration/auto-login/';
    }
}
