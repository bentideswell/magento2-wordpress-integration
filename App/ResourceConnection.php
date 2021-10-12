<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

class ResourceConnection
{
    /**
     * @var []
     */
    private $connection = [];

    /**
     * @var []
     */
    private $tablePrefix = [];

    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @param  \FishPig\WordPress\App\Config $config
     * @param  \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\Config $config,
        \FishPig\WordPress\App\ResourceConnection\ConfigRetriever $connectionConfigRetriever,
        \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->appMode = $appMode;
        $this->config = $config;
        $this->connectionConfigRetriever = $connectionConfigRetriever;
        $this->connectionFactory = $connectionFactory;
        $this->storeManager = $storeManager;

        if ($this->appMode->isApiMode()) {
            throw new \Exception('Cannot use the ResourceConnection in API mode.');
        }
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->getConnection()->isConnected();
    }
    
    /**
     * @return false|\Magento\Framework\App\ResourceConnection\Connection
     */
    public function getConnection()
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        
        if (!isset($this->connection[$storeId])) {
            $this->connection[$storeId] = false;
            
            $config = $this->connectionConfigRetriever->getConfig();
            
            $this->tablePrefix[$storeId] = $config['table_prefix'];
            $db = $this->connection[$storeId] = $this->connectionFactory->create($config);
            
            // Set the correct charset
            $this->connection[$storeId]->query(
                $this->connection[$storeId]->quoteInto('SET NAMES ?', $config['charset'])
            );
        }

        return $this->connection[$storeId];
    }
    
    /**
     * @param  string $table
     * @param  bool $canBeUsedInNetwork = true
     * @return string
     */
    public function getTableName($table, $canBeUsedInNetwork = true): string
    {
        if (!$canBeUsedInNetwork) {
            return $this->getTablePrefix() . $table;
        }
        
        return $this->getTablePrefix() . $table;        
    }

    /**
     * @return string
     */
    private function getTablePrefix(): string
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        return isset($this->tablePrefix[$storeId]) ? $this->tablePrefix[$storeId] : '';
    }
}
