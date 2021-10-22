<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Model;

use FishPig\WordPress\App\Logger;
use FishPig\WordPress\Block\ShortcodeFactory;
use FishPig\WordPress\Model\OptionRepository;
use FishPig\WordPress\App\Url;
use FishPig\WordPress\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;

class Context
{   
    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var ShortcodeFactory
     */
    private $shortcodeFactory;
    
    /**
     * @var OptionRepository
     */
    private $optionRepository;
    
    /**
     * @var Url
     */
    private $url;
    
    /**
     * @var PostCollectionFactory
     */
    private $postCollectionFactory;

    /**
     * @param Registry
     */
    public function __construct(
        Logger $logger,
        ShortcodeFactory $shortcodeFactory,
        OptionRepository $optionRepository,
        Url $url,
        PostCollectionFactory $postCollectionFactory
    ) {
        $this->logger = $logger;
        $this->shortcodeFactory = $shortcodeFactory;
        $this->optionRepository = $optionRepository;
        $this->url = $url;
        $this->postCollectionFactory = $postCollectionFactory;
    }

    /**
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
    
    /**
     * @return ShortcodeFactory
     */
    public function getShortcodeFactory(): ShortcodeFactory
    {
        return $this->shortcodeFactory;
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
    
    /**
     * @return PostCollectionFactory
     */
    public function getPostCollectionFactory(): PostCollectionFactory
    {
        return $this->postCollectionFactory;
    }
}
