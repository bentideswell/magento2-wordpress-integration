<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

use Magento\Framework\App\ResourceConnection\ConnectionFactory;
use FishPig\WordPress\Model\WPConfig;
use FishPig\WordPress\Model\Network;
use Magento\Store\Model\StoreManagerInterface;
use FishPig\WordPress\Model\Logger;

class ResourceConnection
{
    /**
     * @var 
     */
    protected $connectionFactory;

    /**
     * @var 
     */
    protected $tablePrefix = [];

    /**
     * @var 
     */
    protected $connection = [];

    /**
     * @var Network
     */
    protected $network;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var 
     */
    protected $_tables = [];

    /**
     * @var 
     */
    public function __construct(
        ConnectionFactory $connectionFactory, 
        WPConfig $wpConfig, 
        Network $network, 
        StoreManagerInterface $storeManager,
        Logger $logger
    )
    {
        $this->connectionFactory = $connectionFactory;
        $this->network = $network;
        $this->wpConfig = $wpConfig;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @return
     */
    protected function loadByStoreId($storeId)
    {        
        $storeId = (int)$storeId;

        if (isset($this->connection[$storeId])) {
            return $this;
        }        

        $this->connection[$storeId]  = false;
        $this->tablePrefix[$storeId] = $this->wpConfig->getData('DB_TABLE_PREFIX');

        $this->applyMapping([
            'wordpress_menu'              => 'terms',
            'wordpress_menu_item'         => 'posts',
            'wordpress_post'              => 'posts',
            'wordpress_post_meta'         => 'postmeta',
            'wordpress_post_comment'      => 'comments',
            'wordpress_post_comment_meta' => 'commentmeta',
            'wordpress_option'            => 'options',
            'wordpress_term'              => 'terms',
            'wordpress_term_relationship' => 'term_relationships',
            'wordpress_term_taxonomy'     => 'term_taxonomy',
            'wordpress_user'              => 'users',
            'wordpress_user_meta'         => 'usermeta',
        ]);

        $db = $this->connection[$storeId] = $this->connectionFactory->create([
            'host' => $this->wpConfig->getDbHost(),
            'dbname' => $this->wpConfig->getDbName(),
            'username' => $this->wpConfig->getDbUser(),
            'password' => $this->wpConfig->getDbPassword(),
            'active' => '1',    
        ]);

        $this->connection[$storeId]->query('SET NAMES UTF8');

        if ($networkTables = $this->network->getNetworkTables()) {
            $this->applyMapping($networkTables);
        }

        $this->updateMagentoDataInWordPress();
    }

    /**
     * @return $this
     */
    public function updateMagentoDataInWordPress()
    {
        // Pass some data to WordPress via it's options table
        if ((int)$this->storeManager->getStore()->getId() > 0) {
            $optionName  = 'fishpig_magento_base_url';
            $optionTable = $this->getTable('wordpress_option');
            $baseUrl     = $this->storeManager->getStore()->getBaseUrl();

            try {
                $this->_setOptionValue('fishpig_magento', json_encode([
                    'base_url' => $this->storeManager->getStore()->getBaseUrl(),
                    'version' => 2
                ]));
            }
            catch (\Exception $e) {
                $this->logger->error($e);
            }
        }
        
        return $this;
    }
    
    /**
     * @param  string $optionName
     * @param  mixed  $newValue
     * @return void
     */
    protected function _setOptionValue($optionName, $newValue)
    {
        $db = $this->getConnection();
        $optionTable = $this->getTable('wordpress_option');

        $optionData = $db->fetchRow(
            $db->select()->from($optionTable, ['option_id', 'option_value'])->where('option_name=?', $optionName)->limit(1)
        );

        if ($optionData) {
            list($optionId, $optionValue) = array_values($optionData);

            if ($optionValue !== $newValue) {
                $db->update($optionTable, ['option_value' => $newValue], 'option_id=' . (int)$optionId);
            }
        }
        else {
            $db->insert($optionTable, ['option_name' => $optionName, 'option_value' => $newValue]);
        }
    }

    /**
     * @param  array
     * @return $this
     */
    protected function applyMapping($tables)
    {
        $storeId = $this->getStoreId();

        $this->loadByStoreId($storeId);

        foreach($tables as $alias => $table) {
            $this->tables[$storeId][$alias] = $this->getTablePrefix() . $table;
        }

        return $this;
    }

    /**
     * Convert a table alias to a full table name
     *
     * @param string $alias
     * @return string
     */
    public function getTable($alias)
    {
        $storeId = $this->getStoreId();

        $this->loadByStoreId($storeId);

        if (($key = array_search($alias, $this->tables[$storeId])) !== false) {
            if (strpos($key, 'wordpress_') === 0) {
                return $alias;
            }
        }

        return isset($this->tables[$storeId][$alias]) ? $this->tables[$storeId][$alias] : $this->getTablePrefix() . $alias;
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->getConnection() !== false;
    }

    /**
     *
     *
     * @return 
     */
    public function getConnection()
    {
        $storeId = $this->getStoreId();

        $this->loadByStoreId($storeId);

        return isset($this->connection[$storeId]) ? $this->connection[$storeId] : false;
    }

    /**
     * @return 
     */
    public function getTablePrefix()
    {
        $storeId = $this->getStoreId();

        $this->loadByStoreId($storeId);

        return $this->tablePrefix[$this->getStoreId()];
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->storeManager->getStore()->getId();
    }
}
