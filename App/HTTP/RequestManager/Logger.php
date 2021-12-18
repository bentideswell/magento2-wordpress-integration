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
     * @const string
     */
    const LOG_SPACER = '   ';

    /**
     * @param  array $requestData
     * @return void
     */
    public function logApiRequest(array $requestData): void
    {
        $this->addRecord(
            self::INFO, 
            implode(
                self::LOG_SPACER,
                array_merge(
                    [
                        'remote_addr' => str_pad($this->getRemoteAddress() ?: '--', 15, ' ', STR_PAD_LEFT),
                        'date' => date('Y/m/d H:i:s')
                    ],
                    array_values(
                        array_filter($requestData)
                    )
                )
            )
        );
    }
    
    /**
     * @return string|false
     */
    private function getRemoteAddress()
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                return $_SERVER[$header];
            }
        }

        return false;
    }
}
