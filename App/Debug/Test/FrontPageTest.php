<?php
/**
 * @package FishPig_WordPress
 * @author  Ben Tideswell (ben@fishpig.com)
 * @url     https://fishpig.co.uk/magento/wordpress-integration/
 */
declare(strict_types=1);

namespace FishPig\WordPress\App\Debug\Test;

class FrontPageTest implements \FishPig\WordPress\App\Debug\TestInterface
{
    /**
     *
     */
    public function __construct(
        \FishPig\WordPress\Helper\FrontPage $frontPage
    ) {
        $this->frontPage = $frontPage;
    }
    
    /**
     * @return void
     */
    public function run(array $options = []): void
    {
        $this->frontPage->getFrontPage();
        $this->frontPage->getPostsPage();
        $this->frontPage->isFrontPageDefaultPostTypeArchive();
        $this->frontPage->isFrontPageStaticPage();
        $this->frontPage->getFrontPageId();
        $this->frontPage->getPostsPageId();
        $this->frontPage->getRealHomepageUrl();
    }
}
