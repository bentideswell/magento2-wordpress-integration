<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Collection;

use FishPig\WordPress\App\Logger;
use FishPig\WordPress\App\Option;

class Context
{
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var Option
     */
    private $option;
    
    /**
     * @param Registry
     */
    public function __construct(
        Logger $logger,
        Option $option
    ) {
        $this->logger = $logger;
        $this->option = $option;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * @return Option
     */
    public function getOption(): Option
    {
        return $this->option;
    }
}