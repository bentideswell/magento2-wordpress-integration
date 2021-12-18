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
     *
     */
    public function __construct(
        $name,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        array $handlers = array(),
        array $processors = array()
    ) {
        $this->remoteAddress = $remoteAddress;
        parent::__construct($name, $handlers, $processors);
    }

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
                        'remote_addr' => str_pad($this->remoteAddress->getRemoteAddress() ?: '--', 15, ' ', STR_PAD_LEFT),
                        'date' => date('Y/m/d H:i:s')
                    ],
                    array_values(
                        array_filter($requestData)
                    )
                )
            )
        );
    }
}
