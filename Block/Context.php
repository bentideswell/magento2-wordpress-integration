<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Block;

use Magento\Framework\Registry;
use FishPig\WordPress\App\Logger;
use FishPig\WordPress\Block\ShortcodeFactory;
use FishPig\WordPress\Model\OptionRepository;
use FishPig\WordPress\App\Url;
use Magento\Framework\Serialize\SerializerInterface;
use FishPig\WordPress\Helper\BlogInfo;

class Context
{
    /**
     * @var Registry
     */
    private $registry;
    
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
     * @var SerializerInterface
     */
    private $serializer;
    
    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;
    
    /**
     * @var BlogInfo
     */
    private $blogInfo;

    /**
     * @param Registry
     */
    public function __construct(
        Registry $registry,
        Logger $logger,
        ShortcodeFactory $shortcodeFactory,
        OptionRepository $optionRepository,
        Url $url,
        SerializerInterface $serializer,
        SerializerInterface $jsonSerializer,
        BlogInfo $blogInfo
    ) {
        $this->registry = $registry;
        $this->logger = $logger;
        $this->shortcodeFactory = $shortcodeFactory;
        $this->optionRepository = $optionRepository;
        $this->url = $url;
        $this->serializer = $serializer;
        $this->jsonSerializer = $jsonSerializer;
        $this->blogInfo = $blogInfo;
    }
    
    /**
     * @return Registry
     */
    public function getRegistry(): Registry
    {
        return $this->registry;
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
     * @return Serializer
     */
    public function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }

    /**
     * @return Serializer
     */
    public function getJsonSerializer(): SerializerInterface
    {
        return $this->jsonSerializer;
    }
    
    /**
     * @return BlogInfo
     */
    public function getBlogInfo(): BlogInfo
    {
        return $this->blogInfo;
    }
}
