<?php
/**
 *
 */
namespace FishPig\WordPress\Model\Sitemap\ItemProvider;

use FishPig\WordPress\Model\Factory;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use FishPig\WordPress\Helper\Core as CoreHelper;
use FishPig\WordPress\Model\Network;

abstract class AbstractItemProvider/** implements ItemProviderInterface*/
{    
    /**
     * @var \FishPig\WordPress\Model\Factory
     */
    protected $factory;

    /**
     * @var \Magento\Store\Model\App\Emulation;
     */
    protected $emulation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     *
     *
     */
    public function __construct(
        Factory $factory,
        Emulation $emulation,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        CoreHelper $coreHelper,
        Network $network
    )
    {
        $this->emulation = $emulation;
        $this->factory = $factory;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->coreHelper = $coreHelper;
        $this->network = $network;

        // OM required as SitemapItemInterfaceFactory is not present in Magento 2.2 and below so constructor injection breaks compilation
        $this->itemFactory = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sitemap\Model\SitemapItemInterfaceFactory');
    }

    /**
     * @param int $storeId
     */
    final public function getItems($storeId)
    {
        try {
             $items = [];

            $this->emulation->startEnvironmentEmulation($storeId);

            $isNetworkAndHaveCoreHelper = $this->network->isEnabled() && ($coreHelper = $this->coreHelper->getHelper());

            if ($isNetworkAndHaveCoreHelper) {
                $coreHelper->simulatedCallback(function($blogId) {
                    if ((int)$blogId !== (int)get_current_blog_id()) {
                        switch_to_blog($blogId);
                    }
                    }, [$this->network->getBlogId()]
                );
            }

            if ($this->isEnabledForStore($storeId)) {
                $items = $this->_getItems($storeId);
            }

            $this->emulation->stopEnvironmentEmulation();

            if ($isNetworkAndHaveCoreHelper) {
                $coreHelper->simulatedCallback(function() {
                    if (function_exists('restore_current_blog')) {
                        restore_current_blog();
                    }
                });
            }

            return $items;
        }
        catch (\Exception $e) {
            $this->emulation->stopEnvironmentEmulation();

            throw $e;
        }
    }

    /**
     * @param int $storeId
     * @return bool
     */
    protected function isEnabledForStore($storeId)
    {
        return (int)$this->scopeConfig->getValue('wordpress/xmlsitemap/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId) === 1;
    }
}
