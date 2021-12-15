<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class Tests
{
    /**
     * @var array
     */
    private $result = [];
    private $warnings = [];

    /**
     * @var array
     */
    private $integrationTestPool;

    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Logger $logger,
        array $integrationTestPool = []
    ) {
        $this->appMode = $appMode;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->integrationTestPool = $integrationTestPool;
    }
    
    /**
     * @return bool
     * @throws \Exception
     */
    public function runTests($forceRunIfAlreadyRan = false): bool
    {
        if ($this->appMode->isDisabled()) {
            return false;
        }

        $storeId = (int)$this->storeManager->getStore()->getId();

        if ($forceRunIfAlreadyRan || !isset($this->result[$storeId])) {
            $this->result[$storeId] = false;
            $this->warnings[$storeId] = [];

            try {
                foreach ($this->integrationTestPool as $integrationTest) {
                    try {
                        $integrationTest->runTest();
                    } catch (IntegrationRecoverableException $e) {
                        $this->logger->warning($e);
                        $this->warnings[$storeId][] = $e;
                    }
                }

                $this->result[$storeId] = true;
            } catch (\Exception $e) {
                $this->result[$storeId] = $e;
                $this->logger->error($e);
            }
        }

        if ($this->result[$storeId] instanceof \Exception) {
            throw $this->result[$storeId];
        }

        return $this->result[$storeId];
    }
    
    /**
     * @return array|false
     */
    public function getWarnings()
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        return !empty($this->warnings[$storeId]) ? $this->warnings[$storeId] : false;
    }
}
