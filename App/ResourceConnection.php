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
     * @var []
     */
    private $tableMap = [];

    /**
     * @var []
     */
    private $legacyTableMap = [
        'wordpress_post' => 'posts',
        'wordpress_post_meta' => 'postmeta',
        'wordpress_term' => 'terms',
        'wordpress_term_taxonomy' => 'term_taxonomy',
        'wordpress_term_relationship' => 'term_relationships',
        'wordpress_user' => 'users',
        'wordpress_user_meta' => 'usermeta',
        'wordpress_post_comment' => 'comments',
        'wordpress_post_comment_meta' => 'commentmeta',
    ];

    /**
     * @param  \FishPig\WordPress\App\Integration\Mode $appMode
     * @param  \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\Integration\Mode $appMode,
        \FishPig\WordPress\App\ResourceConnection\ConfigRetriever $connectionConfigRetriever,
        \Magento\Framework\App\ResourceConnection\ConnectionFactory $connectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \FishPig\WordPress\App\Cache $cache
    ) {
        $this->appMode = $appMode;

        if ($this->appMode->isApiMode()) {
            throw new \FishPig\WordPress\App\Exception(
                'Cannot use the ResourceConnection in API mode.'
            );
        }

        $this->connectionConfigRetriever = $connectionConfigRetriever;
        $this->connectionFactory = $connectionFactory;
        $this->storeManager = $storeManager;
        $this->cache = $cache;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->getConnection() && $this->getConnection()->isConnected();
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

            if (isset($config['ssl'])) {
                if ((int)$config['ssl'] !== 0) {
                    if (empty($config['driver_options'])) {
                        $config['driver_options'] = [];
                    }

                    $config['driver_options'][\PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
                    $config['driver_options'][\PDO::MYSQL_ATTR_SSL_CA] = true;
                }

                unset($config['ssl']);
            }

            $this->tablePrefix[$storeId] = $config['table_prefix'];
            $db = $this->connection[$storeId] = $this->connectionFactory->create($config);

            $db->query(
                $db->quoteInto('SET NAMES ?', $config['charset'])
            );
            
            unset($config['driver_options']);
            // phpcs:ignore -- not cryptographic
            $tablesExistCacheKey = md5($storeId . '::' . implode(':', $config));

            if ((int)$this->cache->load($tablesExistCacheKey) !== 1) {
                $targetTable = $this->getTable('posts');

                $tableExists = false !== $db->fetchOne(
                    $db->select()
                        ->from('information_schema.tables', 'TABLE_NAME')
                        ->where('table_schema = ?', $config['dbname'])
                        ->where('table_name  = ?', $targetTable)
                        ->limit(1)
                );
   
                if (!$tableExists) {
                    throw new \FishPig\WordPress\App\Exception(
                        "Database connected but table '$targetTable' does not exist."
                    );
                }

                $this->cache->save('1', $tablesExistCacheKey, [], 14400 /* 4 hours */);
            }
        }

        return $this->connection[$storeId];
    }

    /**
     * @param  string $table
     * @param  bool $canBeUsedInNetwork = true
     * @return string
     */
    public function getTable($table, bool $canBeUsedInNetwork = true): string
    {
        // This setups up the connection
        $this->isConnected();
        
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (isset($this->legacyTableMap[$table])) {
            $table = $this->legacyTableMap[$table];
        }

        if (!isset($this->tableMap[$storeId])) {
            $this->tableMap[$storeId] = [];
        } elseif (isset($this->tableMap[$storeId][$table])) {
            return $this->tableMap[$storeId][$table];
        }

        if ($canBeUsedInNetwork) {
            $tablePrefix = $this->getNetworkTablePrefix();
        } else {
            $tablePrefix = $this->getTablePrefix();
        }

        $mappedTable = $tablePrefix . $table;

        return $this->tableMap[$storeId][$table] = $mappedTable;
    }
    
    /**
     * @return string
     */
    public function getTablePrefix(): string
    {
        $storeId = (int)$this->storeManager->getStore()->getId();

        return isset($this->tablePrefix[$storeId]) ? $this->tablePrefix[$storeId] : '';
    }

    /**
     * @return string
     */
    public function getNetworkTablePrefix(): string
    {
        return $this->getTablePrefix();
    }
}
