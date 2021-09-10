<?php
/**
 *
 *
 */
namespace FishPig\WordPress\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use FishPig\WordPress\Model\IntegrationManager;
use FishPig\WordPress\Model\Url;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\Module\Manager as ModuleManager;
use FishPig\WordPress\Model\Plugin;
use FishPig\WordPress\Model\WPConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class Integrate extends \Magento\Backend\Block\Template
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
    protected $messages = [];

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
        WPConfig $wpConfig,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        array $data = []
    ) {
        $this->integrationManager = $integrationManager;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->emulator = $emulator;
        $this->moduleManager = $moduleManager;
        $this->plugin = $plugin;
        $this->wpConfig = $wpConfig;
        $this->scopeConfig = $scopeConfig;
        $this->moduleDirReader = $moduleDirReader;
        
        parent::__construct($context, $data);
    
        try {
            $storeId = 0;

            if (($websiteId = (int)$this->_request->getParam('website')) !== 0) {
                $storeId = (int)$this->storeManager->getWebsite($websiteId)->getDefaultStore()->getId();
            }

            if ($storeId === 0) {
                $storeId = (int)$this->_request->getParam('store');
            }

            if ($storeId === 0) {
                if (!$this->scopeConfig->isSetFlag('wordpress/setup/enabled')) {
                    // We are at global scope and module is disabled here so return
                    return;
                }

                $storeId = (int)$this->storeManager->getDefaultStoreView()->getId();
            }

            $this->emulator->startEnvironmentEmulation($storeId);

            $this->wpConfigFilePath = $this->wpConfig->getConfigFilePath();

            if ($this->integrationManager->runTests() === true) {
                $this->addMessage(
                    sprintf(
                        'WordPress Integration is active. View your blog at <a href="%s" target="_blank">%s</a>.',
                        $this->url->getHomeUrl(),
                        $this->url->getHomeUrl()
                    ),
                    'success'
                );
            }

            $this->validateYoastSeo();
            
            $this->emulator->stopEnvironmentEmulation();
        } catch (\Exception $e) {
            $this->emulator->stopEnvironmentEmulation();
            $this->addMessage($e->getMessage(), 'error');
        }
    }

    /**
     *
     */
    protected function _beforeToHtml()
    {
        $this->setTemplate('FishPig_WordPress::integrate.phtml');
        
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
        
        return parent::_toHtml();
    }

    /**
     *
     */
    private function addMessage($msg, $type)
    {
        $this->messages[] = [
            'type' => $type,
            'msg' => $msg
        ];
        
        return $this;
    }

    /**
     *
     */
    public function getMessages()
    {
        return $this->messages;
    }
    
    /**
     * @return string
     */
    public function getAfterMessagesHtml()
    {
        $moduleVersion = $this->getModuleVersion('FishPig_WordPress');

        $configMsg = 'Unable to find <span style="color:#df4343;">wp-config.php</span> using the Path provided. You can modify the Path below.';
            
        if (isset($this->wpConfigFilePath) && ($configFile = $this->wpConfigFilePath)) {
            $relConfigFile = str_replace(BP, '.', $configFile);
            
            if (is_file($configFile)) {
                $configMsg = 'The <strong>wp-config.php</strong> file has been loaded from <span style="color:#109910;" title="' . $configFile . '">' . $relConfigFile . '</span>';
            } else {
                $configMsg = 'The <strong>wp-config.php</strong> file can not be found at <span style="color:#df4343;" title="' . $configFile . '">' . $relConfigFile . '</span>';
            }
        }

        return "
        <script>
            require(['jquery'], function($){
                $(document).ready(function() {
                    document.getElementById('wordpress_setup-head').innerHTML = 'FishPig WordPress Integration - " . $moduleVersion . "';
                    
                    var configMsg = document.createElement('p');
                    configMsg.classList.add('wp-config-msg');
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
    protected function validateYoastSeo()
    {
        $yoastPluginEnabled = $this->plugin->isEnabled('wordpress-seo/wp-seo.php');
        $yoastModuleEnabled = $this->moduleManager->isEnabled('FishPig_WordPress_Yoast');

        if (!$yoastPluginEnabled && !$yoastModuleEnabled) {
            $this->addMessage(
                sprintf(
                    'For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a> and the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.',
                    self::YOAST_SEO_PLUGIN_URL,
                    self::YOAST_SEO_MODULE_URL
                ),
                'notice'
            );
        } elseif (!$yoastPluginEnabled) {
            $this->addMessage(
                sprintf('For the best SEO results, you should install the free <a href="%s" target="_blank">Yoast SEO WordPress plugin</a>.', 'https://wordpress.org/plugins/wordpress-seo/'),
                'notice'
            );
        } elseif (!$yoastModuleEnabled) {
            $this->addMessage(
                sprintf(
                    'You have installed the Yoast SEO plugin in WordPress. To complete the SEO integration, install the free <a href="%s" target="_blank">Yoast SEO Magento extension</a>.',
                    self::YOAST_SEO_MODULE_URL
                ),
                'notice'
            );
        }
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
        
        if (!is_file($moduleComposerJsonFile)) {
            return false;
        }
        
        $moduleComposerJsonData = json_decode(
            file_get_contents($moduleComposerJsonFile),
            true
        );
        
        return !empty($moduleComposerJsonData['version']) ? $moduleComposerJsonData['version'] : false;
    }
}
