<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\External;

class Option
{
    /**
     * @var []
     */
    private $cache = [];
    
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\App\ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $default;

            $db = $this->resourceConnection->getConnection();

            $value = $db->fetchOne(
                $db->select()
                    ->from($this->resourceConnection->getTable('options'), 'option_value')
                    ->where('option_name=?', $key)
                    ->limit(1)
            );

            if ($value !== false) {
                $this->cache[$key] = $value;
            }
        }

        return $this->cache[$key];
    }
}
