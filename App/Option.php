<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App;

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
    public function get(string $key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $default;

            $db = $this->resourceConnection->getConnection();

            $value = $db->fetchOne(
                $db->select()
                    ->from($this->getOptionsTable(), 'option_value')
                    ->where('option_name=?', $key)
                    ->limit(1)
            );

            if ($value !== false) {
                $this->cache[$key] = $value;
            }
        }

        return $this->cache[$key];
    }

    /**
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $db = $this->resourceConnection->getConnection();

        if ($value === null) {
            // Delete existing value
            $db->delete($this->getOptionsTable(), $db->quoteInto('option_name=?', $key));
            
            unset($this->cache[$key]);
        } else {
            if (is_array($value)) {
                $value = serialize($value);
            }

            if (($existingValue = $this->get($key)) === null) {
                $db->insert(
                    $this->getOptionsTable(),
                    [
                        'option_name' => $key,
                        'option_value' => $value
                    ]
                );
            } else {
                $db->update(
                    $this->getOptionsTable(),
                    ['option_value' => $value],
                    $db->quoteInto('option_name=?', $key)
                );
            }
            
            $this->cache[$key] = $value;
        }
    }
    
    /**
     * @return string
     */
    private function getOptionsTable(): string
    {
        return $this->resourceConnection->getTable('options');
    }
}
