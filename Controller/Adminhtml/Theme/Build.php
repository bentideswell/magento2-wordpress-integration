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
        \Magento\Backend\App\Action\Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return
     */
    public function execute()
    {
        echo 'Run the following CLI command:<br/>';
        echo 'bin/magento fishpig:wordpress:theme --zip';
        exit;
    }
}
