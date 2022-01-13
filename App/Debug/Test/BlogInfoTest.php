<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class BlogInfoTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Helper\BlogInfo $blogInfo
    ) {
        $this->blogInfo = $blogInfo;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $this->blogInfo->getBlogName();
        $this->blogInfo->getBlogDescription();
        $this->blogInfo->isBlogPublic();
    }
}
