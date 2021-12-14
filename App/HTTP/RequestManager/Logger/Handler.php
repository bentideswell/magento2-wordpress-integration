<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\HTTP\RequestManager\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystem,
        $filePath = null,
        $fileName = null
    ) {
        parent::__construct($filesystem, $filePath, $fileName);
        $this->setFormatter(new \Monolog\Formatter\LineFormatter("%message%\n", null, true));
    }
}
