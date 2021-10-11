<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration;

class Tests
{
    /**
     * @var array
     */
    private $result = [];
    
    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\Model\Logger $logger,
        array $integrationTests = []
    ) {
        $this->appMode = $appMode;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->integrationTests = $integrationTests;
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

            try {
                foreach ($this->integrationTests as $integrationTest) {
                    $integrationTest->runTest();
                }
    
                $this->result[$storeId] = true;
            } catch (Exception $e) {
                $this->result[$storeId] = $e;
                $this->logger->error($e);
            }
        }
        
        if ($this->result[$storeId] instanceof Exception) {
            throw $this->result[$storeId];
        }

        return $this->result[$storeId];
    }
}
