<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\RequestManager;

class Logger extends \Monolog\Logger
{
    /**
     * @param  array $requestData
     * @return void
     */
    public function logApiRequest(array $requestData): void
    {
        $this->addRecord(
            self::INFO, 
            implode(
                '    ',
                array_merge(
                    [
                        date('Y/m/d H:i:s')
                    ],
                    array_values(
                        array_filter($requestData)
                    )
                )
            )
        );
    }
}
