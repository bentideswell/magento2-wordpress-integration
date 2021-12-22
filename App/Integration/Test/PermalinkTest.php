<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;

class PermalinkTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @param \FishPig\WordPress\App\Option $option
     */
    public function __construct(
        \FishPig\WordPress\App\Option $option
    ) {
        $this->option = $option;
    }
    
    /**
     * @return void
     */
    public function runTest(): void
    {
        $optionName = 'permalink_structure';

        if (!$this->option->get($optionName)) {
            $this->option->set($optionName, '/%postname%/');

            if (!$this->option->get($optionName)) {
                throw new IntegrationRecoverableException(
                    sprintf(
                        'A custom permalink structure is not set. Set a custom permalink structure in the WP Admin',
                        'https://fishpig.co.uk/magento/wordpress-integration/installation/'
                    )
                );
            }
        }
    }
}
