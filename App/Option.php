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
     * @param \FishPig\WordPress\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \FishPig\WordPress\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->serializer = $serializer;
    }

    /**
     * @param  string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $db = $this->resourceConnection->getConnection();

        return $db->fetchOne(
            $db->select()
                ->from($this->getOptionsTable(), 'option_value')
                ->where('option_name=?', $key)
                ->limit(1)
        ) ?: null;
    }

    /**
     * @param  string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $db = $this->resourceConnection->getConnection();

        return false !== $db->fetchRow(
            $db->select()
                ->from($this->getOptionsTable(), 'option_value')
                ->where('option_name=?', $key)
                ->limit(1)
        );
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
        } else {
            if (is_array($value)) {
                $value = $this->serializer->serialize($value);
            }

            if ($this->exists($key) === false) {
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
