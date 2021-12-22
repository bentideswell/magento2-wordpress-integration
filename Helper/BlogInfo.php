<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\Helper;

class BlogInfo extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \FishPig\WordPress\Model\OptionRepository $optionRepository
    ) {
        $this->optionRepository = $optionRepository;
        parent::__construct($context);
    }

    /**
     * @return string
     */
    public function getBlogName(): string
    {
        return $this->optionRepository->get('blogname', '');
    }
    
    /**
     * @return string
     */
    public function getBlogDescription(): string
    {
        return $this->optionRepository->get('blogdescription', '');
    }
    
    /**
     * @return bool
     */
    public function isBlogPublic(): bool
    {
        return (bool)$this->optionRepository->get('blog_public') ?? false;
    }
}
