<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel;

use FishPig\WordPress\App\Logger;
use FishPig\WordPress\Model\OptionRepository;
use FishPig\WordPress\App\Url;
use FishPig\WordPress\App\ResourceConnection;

class Context
{
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    
    /**
     * @var Url
     */
    private $url;
    
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param Registry
     */
    public function __construct(
        Logger $logger,
        OptionRepository $optionRepository,
        Url $url,
        ResourceConnection $resourceConnection
    ) {
        $this->logger = $logger;
        $this->optionRepository = $optionRepository;
        $this->url = $url;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * @return ResourceConnection
     */
    public function getResourceConnection(): ResourceConnection
    {
        return $this->resourceConnection;
    }
    
    /**
     * @return Option
     */
    public function getOptionRepository(): OptionRepository
    {
        return $this->optionRepository;
    }
    
    /**
     * @return Url
     */
    public function getUrl(): Url
    {
        return $this->url;
    }
}
