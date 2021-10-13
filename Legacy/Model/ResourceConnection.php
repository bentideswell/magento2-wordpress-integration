<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Legacy\Model;

class ResourceConnection
{
    /**
     * @var
     */
    public function __construct(\FishPig\WordPress\App\ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param  string $alias
     * @return string
     */
    public function getTable($alias)
    {
        return $this->resourceConnection->getTableName($alias);
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        return $this->resourceConnection->isConnected();
    }

    /**
     *
     *
     * @return
     */
    public function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * @return
     */
    public function getTablePrefix()
    {
        return $this->resourceConnection->getTablePrefix();
    }
}
