<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\ResourceConnection;

class ConfigRetriever extends \FishPig\WordPress\App\Integration\Mode\ObjectResolver
{
    /**
     * @return array
     */
    public function getConfig(): array
    {
        $config = array_merge(
            $this->getConfigDefaults(),
            (array)$this->getObject()->getDatabaseConfig()
        );
        
        if ($missingFields = array_diff_key($this->getRequiredFields(), $config)) {
            throw new \FishPig\WordPress\App\Exception(
                'Missing database configuration fields: ' . implode(', ', array_flip($missingFields))
            );
        }
        
        return $config;
    }

    /**
     * @return []
     */
    private function getConfigDefaults(): array
    {
        return [
            'charset' => 'utf8',
        ];
    }

    /**
     * @return []
     */
    private function getRequiredFields(): array
    {
        return array_flip(
            [
                'host',
                'dbname',
                'username',
                'password',
                'charset',
                'table_prefix',
            ]
        );
    }
}
