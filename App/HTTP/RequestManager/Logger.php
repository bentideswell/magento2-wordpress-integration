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
        $requestData = array_filter($requestData, function($x) {
            return (string)$x !== '';
        });

        $requestData = array_map(function($v, $i) {
            return str_pad((string)$i, 8, ' ', STR_PAD_LEFT) . ':  ' . $v;
        }, $requestData, array_keys($requestData));

        $logMsg = implode("\n", $requestData);

        $this->addRecord(self::INFO, $logMsg . "\n" . str_repeat('-', 90));
    }
}
