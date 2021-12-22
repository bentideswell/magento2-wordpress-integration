<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model\ResourceModel\Collection;

class Context
{
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var Option
     */
    private $optionRepository;
    
    /**
     * @param Registry
     */
    public function __construct(
        \FishPig\WordPress\App\Logger $logger,
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->logger = $logger;
        $this->optionRepository = $optionRepository;
    }

    /**
     * @return Logger
     */
    public function getLogger(): \FishPig\WordPress\App\Logger
    {
        return $this->logger;
    }
    
    /**
     * @return \FishPig\WordPress\Model\OptionRepository
     */
    public function getOptionRepository(): \FishPig\WordPress\Model\OptionRepository
    {
        return $this->optionRepository;
    }
    
    /**
     * @return \FishPig\WordPress\Model\OptionRepository
     */
    public function getOption(): \FishPig\WordPress\Model\OptionRepository
    {
        return $this->getOptionRepository();
    }
}
