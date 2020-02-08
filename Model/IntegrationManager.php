<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use FishPig\WordPress\Model\Integration\IntegrationException;
use FishPig\WordPress\Model\Logger;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\State;
use Exception;

class IntegrationManager
{
    /**
     * @var Exception
     */
    protected $exception;

    /**
     * @var IntegrationTests
     */
    protected $integrationTests;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var
     */
    protected $state;

    /**
     *
     */
    public function __construct(array $integrationTests, Logger $logger, ScopeConfigInterface $scopeConfig, StoreManagerInterface $storeManager, State $state)
    {
        $this->integrationTests = $integrationTests;
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->state = $state;
    }

    /**
     * @return $this
     */
    public function runTests()
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!isset($this->exception[$storeId])) {
            $this->exception[$storeId] = false;

            if ($this->isModuleEnabledForScope()) {
                try {
                    foreach($this->integrationTests as $integrationTest) {
                        $integrationTest->runTest();
                    }

                    $this->exception[$storeId] = true;
                }
                catch (Exception $e) {
                    $this->exception[$storeId] = $e;
                    $this->logger->error($e);
                }
            }
        }

        if ($this->exception[$storeId] instanceof Exception) {
            throw $this->exception[$storeId];
        }

        return $this->exception[$storeId];
    }

    /**
     * @return bool
     */
    public function isModuleEnabledForScope()
    {
        return (int)$this->scopeConfig->getValue('wordpress/setup/enabled', 'stores', $this->storeManager->getStore()->getId()) === 1;
    }
}
