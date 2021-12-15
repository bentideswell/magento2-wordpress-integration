<?php
/**
 *
 */
namespace FishPig\WordPress\Model;

class WPConfig
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\WPConfig $wpConfig
    ) {
        $this->wpConfig = $wpConfig;
    }

    /**
     * @param  string|null $key = null
     * @return mixed
     */
    public function getData($key = null)
    {
        return $this->wpConfig->getData($key);
    }
    
    /**
     * Get the DB host
     * Make a small modification for working with .sock connection strings
     *
     * @return string
     */
    public function getDbHost()
    {
        $dbHost = $this->getData('DB_HOST');
        
        if (strpos($dbHost, '.sock') !== false) {
            $dbHost = str_replace('localhost:/', '/', $dbHost);
        }
        
        return $dbHost;
    }
    
    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->getData('DB_NAME');
    }

    /**
     * @return string
     */
    public function getDbUser()
    {
        return $this->getData('DB_USER');
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        return $this->getData('DB_PASSWORD');
    }
    
    /**
     * @return string
     */
    public function getDbCharset()
    {
        return $this->getData('DB_CHARSET') ?? 'utf8mb4';
    }
}
