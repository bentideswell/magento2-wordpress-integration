<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Mode\Subdirectory;

class WPConfig implements \FishPig\WordPress\Api\App\ResourceConnection\ConfigRetrieverInterface
{
    /**
     * @return void
     */
    public function __construct(
    ) {
        
    }
    
    private function load()
    {
        echo 'Need to implement wpconfig' . PHP_EOL;
        echo __METHOD__;
        exit;
    }

    /**
     * @return array
     */
    public function getDatabaseConfig(): array
    {
        $this->load();
        
        return (array)$this->scopeConfig->getValue('wordpress/mode_external/db');
    }
}
