<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Adminhtml\Theme;

class Build extends \Magento\Backend\App\Action
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'FishPig_WordPress::wp';
    
    /**
     * @param  \Magento\Backend\App\Action\Context $context,
     * @param  \FishPig\WordPress\App\Theme\PackagePublisher $packagePublisher
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \FishPig\WordPress\App\Theme\PackagePublisher $packagePublisher
    ) {
        $this->packagePublisher = $packagePublisher;

        parent::__construct($context);
    }

    /**
     * @return
     */
    public function execute()
    {
        return $this->packagePublisher->publish();
    }
}
