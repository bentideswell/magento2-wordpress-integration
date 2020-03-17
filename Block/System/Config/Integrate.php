<?php
/**
 *
 *
 */
namespace FishPig\WordPress\Block\System\Config;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use FishPig\WordPress\Model\IntegrationManager;
use FishPig\WordPress\Model\Url;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\Module\Manager as ModuleManager;
use FishPig\WordPress\Model\Plugin;
use FishPig\WordPress\Model\WPConfig;
use Magento\Framework\Module\ResourceInterface;

class Integrate extends Template
{
    /**
     * @const string
     */
    const YOAST_SEO_PLUGIN_URL = 'https://wordpress.org/plugins/wordpress-seo/';

    /**
     * @const string
     */
    const YOAST_SEO_MODULE_URL = 'https://github.com/bentideswell/magento2-wordpress-integration-yoastseo';

    /**
     * @var \FishPig\WordPress\Model\IntegrationManager
     */
    protected $integrationManager;

    /**
     * @var \FishPig\WordPress\Model\Url
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $emulator;

    /**
     * @var \FishPig\WordPress\Helper\Plugin
     */
    protected $plugin;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */
    protected $resourceInterface;

    /**
     * @var
     */
    protected $exception;

    /**
     * @var bool
     */
    protected $success = false;
    
    /**
     * @var string
     */
    protected $wpConfigFilePath;
    
    /**
     * @var WPConfig
     */
    protected $wpConfig;

    /**
     *
     */
    public function __construct(
        Context $context,
        IntegrationManager $integrationManager,
        Url $url,
        StoreManager $storeManager,
        Emulation $emulator,
        ModuleManager $moduleManager,
        Plugin $plugin,
        ResourceInterface $resourceInterface,
        WPConfig $wpConfig,
        array $data = []
    )
    {
        $this->integrationManager = $integrationManager;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->emulator = $emulator;
        $this->moduleManager = $moduleManager;
        $this->plugin = $plugin;
        $this->resourceInterface = $resourceInterface;
        $this->wpConfig = $wpConfig;

        parent::__construct($context, $data);

        if ($this->_request->getParam('section') !== 'wordpress') {
            return;
        }

        $this->success = false;

        try {
            $storeId = 0;

            if (($websiteId = (int)$this->_request->getParam('website')) !== 0) {
                $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
            }

            if ($storeId === 0) {
                $storeId = (int)$this->_request->getParam('store');
            }

            if ($storeId === 0) {
                $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
            }

            $this->emulator->startEnvironmentEmulation($storeId);

            $this->wpConfigFilePath = $this->wpConfig->getConfigFilePath();

            if ($this->integrationManager->runTests() === true) {
                $this->success = sprintf(
                    'WordPress Integration is active. View your blog at <a href="%s" target="_blank">%s</a>.', 
                    $this->url->getHomeUrl(), 
                    $this->url->getHomeUrl()
                );
            }

            $this->emulator->stopEnvironmentEmulation();
        } 
        catch (\Exception $e) {
            $this->emulator->stopEnvironmentEmulation();
            $this->exception = $e;
        }
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $messages = [];

        if ($exception = $this->exception) {
            $messages[] = $this->_getMessage($exception->getMessage(), 'error');
        }
        else if ($this->success) {
            $messages[] = $this->_getMessage($this->success);

            if ($msg = $this->_getYoastSeoMessage()) {
                $messages[] = $msg;
            }
        }

        if ($messages) {
            return '<div class="messages">' . implode("\n", $messages) . '</div>'. $this->_getExtraHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    protected function _getExtraHtml()
    {
        $moduleVersion = $this->resourceInterface->getDbVersion('FishPig_WordPress');

        $configMsg = 'Unable to find <strong style="color:#df4343;">wp-config.php</strong> using the Path provided. You can modify the Path below.';
            
        if (isset($this->wpConfigFilePath) && ($configFile = $this->wpConfigFilePath)) {
            if (is_file($configFile)) {
                $configMsg = 'The <strong>wp-config.php</strong> file has been loaded from <strong style="color:#109910;">' . $configFile . '</strong>';
            }
            else {
                $configMsg = 'The <strong>wp-config.php</strong> file can not be found at <strong style="color:#df4343;">' . $configFile . '</strong>';
            }
        }

        return "
        <script>
            require(['jquery'], function($){
                $(document).ready(function() {
                    document.getElementById('wordpress_setup-head').innerHTML = 'Magento WordPress Integration - v" . $moduleVersion . "';
                    
                    var configMsg = document.createElement('p');
                    configMsg.innerHTML = '" . $configMsg . "';
                    document.getElementById('wordpress_setup-head').parentNode.appendChild(configMsg);
                });
            });
        </script>
        ";
    }

    /**
     * @return string
     */
    protected function _getYoastSeoMessage()
    {
        $yoastPluginEnabled = $this->plugin->isEnabled('wordpress-seo/wp-seo.php');
        $yoastModuleEnabled = $this->moduleManager->isEnabled('FishPig_WordPress_Yoast');

        if (!$yoastPluginEnabled && !$yoastModuleEnabled) {
            return $this->_getMessage(
                sprintf(
                    'For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a> and the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.', 
                    self::YOAST_SEO_PLUGIN_URL,
                    self::YOAST_SEO_MODULE_URL
                ),
                'notice'
            );
        } 

        if (!$yoastPluginEnabled) {
            return $this->_getMessage(
                sprintf('For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a>.', 'https://wordpress.org/plugins/wordpress-seo/'),
                'notice'
            );
        }

        if (!$yoastModuleEnabled) {
            return $this->_getMessage(
                sprintf(
                    'You have installed the Yoast SEO plugin in WordPress. To complete the SEO integration, install the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.', 
                    self::YOAST_SEO_MODULE_URL
                ),
                'notice'
            );
        }
    }

    /**
     * @return string
     */
    protected function _getMessage($msg, $type = 'success')
    {
        return sprintf('<div class="message message-%s %s"><div>%s</div></div>', $type, $type, $msg);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }
}
