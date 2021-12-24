<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block\Adminhtml\System\Config;

class IntegrationStatus extends \Magento\Backend\Block\Template
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     *
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Integration\Tests $integrationTests,
        \FishPig\WordPress\App\Url $wpUrl,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Store\Model\App\Emulation $emulator,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        array $data = []
    ) {
        $this->appMode = $appMode;
        $this->integrationTests = $integrationTests;
        $this->wpUrl = $wpUrl;
        $this->storeManager = $storeManager;
        $this->emulator = $emulator;
        $this->moduleDirReader = $moduleDirReader;
        parent::__construct($context, $data);
    }

    protected function _beforeToHtml()
    {
        $this->setTemplate('FishPig_WordPress::integration-status.phtml');
        return parent::_beforeToHtml();
    }
    
    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_request->getParam('section') !== 'wordpress') {
            return;
        }
        
        try {
            if (($storeId = $this->getStoreId()) === false) {
                return;
            }
    
            $this->emulator->startEnvironmentEmulation($storeId);
                    
            try {
                if ($this->integrationTests->runTests() !== false) {
                    $homeUrl = $this->wpUrl->getHomeUrl();
                    
                    $this->addMessage(
                        sprintf(
                            'WordPress Integration is active. View your blog at <a href="%s" target="_blank">%s</a>.',
                            $homeUrl,
                            $homeUrl
                        ),
                        'success'
                    );
                }
            } catch (\Exception $e) {
                $this->addMessage($e->getMessage(), 'error');
            }
            
            if ($integrationWarnings = $this->integrationTests->getWarnings()) {
                foreach ($integrationWarnings as $integrationWarning) {
                    $this->addMessage($integrationWarning->getMessage(), 'warning');
                }
            }
            
            $this->emulator->stopEnvironmentEmulation();
        } catch (\Exception $e) {
            $this->addMessage($e->getMessage(), 'error');
        }
        
        return parent::_toHtml();
    }

    /**
     * @return int|false
     */
    private function getStoreId()
    {
        $storeId = 0;

        if (($websiteId = (int)$this->_request->getParam('website')) !== 0) {
            $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
        }

        if ($storeId === 0) {
            $storeId = (int)$this->_request->getParam('store');
        }

        if ($storeId === 0) {
            if ($this->appMode->isDisabled()) {
                // We are at global scope and module is disabled here so return
                return false;
            }

            $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
        }

        return (int)$storeId;
    }
    
    /**
     *
     */
    private function addMessage($msg, $type)
    {
        $this->messages[] = ['type' => $type, 'msg' => $msg];
        
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }
    
    /**
     * @return string
     */
    public function getPageTitle(): string
    {
        return 'FishPig WordPress Integration - Version ' . $this->getModuleVersion('FishPig_WordPress');
    }

    /**
     * Get the module's version from it's composer.json file
     *
     * @param  string $module
     * @return string|false
     */
    private function getModuleVersion($module)
    {
        $moduleComposerJsonFile = $this->moduleDirReader->getModuleDir('', $module) . '/composer.json';
        
        // phpcs:ignore -- is_file (todo)
        if (!is_file($moduleComposerJsonFile)) {
            return false;
        }
        
        $moduleComposerJsonData = json_decode(
            // phpcs:ignore -- file_get_contents (todo)
            file_get_contents($moduleComposerJsonFile),
            true
        );
        
        return !empty($moduleComposerJsonData['version']) ? $moduleComposerJsonData['version'] : false;
    }
}
