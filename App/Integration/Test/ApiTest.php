<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Integration\Test;

use FishPig\WordPress\App\Integration\Exception\IntegrationRecoverableException;
use FishPig\WordPress\App\Integration\Exception\IntegrationFatalException;

class ApiTest implements \FishPig\WordPress\Api\App\Integration\TestInterface
{
    /**
     * @param \FishPig\WordPress\App\Api\IntegrationDataRetriever $integrationDataRetriever
     */
    public function __construct(
        \FishPig\WordPress\App\Api\IntegrationDataRetriever $integrationDataRetriever
    ) {
        $this->integrationDataRetriever = $integrationDataRetriever;
    }

    /**
     * @return void
     */
    public function runTest(): void
    {
        try {
            if (!($data = $this->integrationDataRetriever->getData())) {
                throw new IntegrationFatalException(
                    'Unable to contact the WordPress API.'
                );
            }
        } catch (\FishPig\WordPress\App\HTTP\InvalidStatusException $e) {
            throw new IntegrationFatalException($e->getMessage(), $e->getCode(), $e);
        } catch (\FishPig\WordPress\App\Api\Exception\MissingApiDataException $e) {
            throw new IntegrationFatalException($e->getMessage());
        }
    }
}
