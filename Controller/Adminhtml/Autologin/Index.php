<?php
/**
 *
 */
declare(strict_types=1);

namespace FishPig\WordPress\Controller\Adminhtml\Autologin;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @const string
     */
    const ADMIN_RESOURCE = 'FishPig_WordPress::wp';
    
    /**
     *
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(
            $this->resultFactory::TYPE_PAGE
        );

        $resultPage->setActiveMenu('FishPig_WordPress::wordpress');
        $resultPage->getConfig()->getTitle()->prepend(__('WordPress Admin - Auto Login'));

        return $resultPage;
    }
}
