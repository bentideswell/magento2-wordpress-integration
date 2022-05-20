<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\Local;

use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class WPConfig implements \FishPig\WordPress\Api\App\ResourceConnection\ConfigRetrieverInterface
{
    /**
     * @return void
     */
    public function __construct(
        \FishPig\WordPress\App\WPConfig $wpConfig
    ) {
        $this->wpConfig = $wpConfig;
    }
 
    /**
     * @return array
     */
    public function getDatabaseConfig(): array
    {
        $dbHost = $this->wpConfig->getData('DB_HOST');
        
        if (strpos($dbHost, '.sock') !== false) {
            $dbHost = str_replace('localhost:/', '/', $dbHost);
        }

        return [
            'host' => $dbHost,
            'dbname' => $this->wpConfig->getData('DB_NAME'),
            'username' => $this->wpConfig->getData('DB_USER'),
            'password' => $this->wpConfig->getData('DB_PASSWORD'),
            'charset' => $this->wpConfig->getData('DB_CHARSET', 'utf8mb4'),
            'table_prefix' => $this->wpConfig->getData('DB_TABLE_PREFIX'),
            'ssl' => $this->wpConfig->getData('DB_SSL') || $this->wpConfig->getData('MYSQL_CLIENT_FLAGS')
        ];
    }
}
