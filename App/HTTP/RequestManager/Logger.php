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

    public function __construct(
        $name, 
        \FishPig\WordPress\Model\UrlInterface $url,
        array $handlers = array(), 
        array $processors = array()
    ) {
        $this->url=$url;
        parent::__construct($name, $handlers, $processors);
    }
    /**
     * @param  array $requestData
     * @return void
     */
    public function logApiRequest(array $requestData): void
    {
        $logMsg = implode(
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
        ) . "\n" . str_repeat(' ', 53) . $this->url->getCurrentUrl() . "\n";

        $this->addRecord(self::INFO, $logMsg);
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
